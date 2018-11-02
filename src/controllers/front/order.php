<?php
/**
 * Contains code for the order rest controller.
 */

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\OrderStorageUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\MiscUtil;
use Boxtal\BoxtalConnectPrestashop\Util\OrderUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ProductUtil;

/**
 * Order reset controller.
 *
 * Opens API endpoint to sync orders.
 */
class boxtalconnectOrderModuleFrontController extends ModuleFrontController
{

    /**
     * Processes request.
     *
     * @void
     */
    public function postProcess()
    {

        $entityBody = file_get_contents('php://input');

        AuthUtil::authenticate($entityBody);

        $route = Tools::getValue('route'); // Get route

        if ('order' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$POST:
                        $this->retrieveOrdersHandler();
                        break;

                    default:
                        break;
                }
            }
        } elseif ('shipped' === $route || 'delivered' === $route) {
            $orderId = Tools::getValue('orderId');
            $body = AuthUtil::decryptBody($entityBody);
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$POST:
                        $this->trackingEventHandler($orderId, $route, $body);
                        break;

                    default:
                        break;
                }
            }
        }

        ApiUtil::sendApiResponse(400);
    }

    /**
     * Endpoint callback.
     *
     * @void
     */
    public function retrieveOrdersHandler()
    {
        $response = $this->getOrders();
        echo json_encode($response);
        die();
        ApiUtil::sendApiResponse(200, $response);
    }

    /**
     * Get Prestashop orders.
     *
     * @return array $result
     */
    public function getOrders()
    {
        $orders = OrderUtil::getOrders();
        $result = array();

        foreach ($orders as $order) {
            if (null !== MiscUtil::notEmptyOrNull($order, 'id_order')) {
                $orderId = (int) MiscUtil::notEmptyOrNull($order, 'id_order');
            } else {
                continue;
            }

            $recipient = array(
                'firstname'    => MiscUtil::notEmptyOrNull($order, 'firstname'),
                'lastname'     => MiscUtil::notEmptyOrNull($order, 'lastname'),
                'company'      => MiscUtil::notEmptyOrNull($order, 'company'),
                'addressLine1' => MiscUtil::notEmptyOrNull($order, 'address1'),
                'addressLine2' => MiscUtil::notEmptyOrNull($order, 'address2'),
                'city'         => MiscUtil::notEmptyOrNull($order, 'city'),
                'state'        => MiscUtil::notEmptyOrNull($order, 'state_iso'),
                'postcode'     => MiscUtil::notEmptyOrNull($order, 'postcode'),
                'country'      => MiscUtil::notEmptyOrNull($order, 'country_iso'),
                'phone'        => MiscUtil::notEmptyOrNull($order, 'phone'),
                'email'        => MiscUtil::notEmptyOrNull($order, 'email'),
            );
            $items = OrderUtil::getItemsFromOrder($orderId);
            $products = array();
            foreach ($items as $item) {
                $product                = array();
                $product['weight']      = 0 !== (float) $item['product_weight'] ? (float) $item['product_weight'] : null;
                $product['quantity']    = (int) $item['product_quantity'];
                $product['price']       = (float) $item['product_price'];
                $description = ProductUtil::getProductDescriptionMultilingual((int) $item['product_id']);
                $product['description'] = $description;
                $products[]             = $product;
            }

            $parcelPoint          = null;
            $parcelPointCode     = OrderStorageUtil::get($orderId, 'bxParcelPointCode');
            $parcelPointNetwork = OrderStorageUtil::get($orderId, 'bxParcelPointNetwork');
            if ($parcelPointCode && $parcelPointNetwork) {
                $parcelPoint = array(
                    'code'     => $parcelPointCode,
                    'network' => $parcelPointNetwork,
                );
            }

            $multilingualStatus = OrderUtil::getStatusMultilingual($orderId);
            $multilingualShippingMethod = array();
            $shippingMethodName = MiscUtil::notEmptyOrNull($order, 'shippingMethod');
            foreach (\Language::getLanguages(true) as $lang) {
                $multilingualShippingMethod[str_replace('-', '_', $lang['locale'])] = $shippingMethodName;
            }

            $result[] = array(
                'internalReference'      => $orderId,
                'reference'      => MiscUtil::notEmptyOrNull($order, 'reference'),
                'status'         => array(
                    'key' => OrderUtil::getStatusId($orderId),
                    'translations' => $multilingualStatus,
                ),
                'shippingMethod' => array(
                    'key' => ShippingMethodUtil::getReferenceFromId(OrderUtil::getCarrierId($orderId)),
                    'translations' => $multilingualShippingMethod,
                ),
                'shippingAmount' => MiscUtil::toFloatOrNull(MiscUtil::notEmptyOrNull($order, 'shippingAmount')),
                'creationDate'   => MiscUtil::dateW3Cformat(MiscUtil::notEmptyOrNull($order, 'creationDate')),
                'orderAmount'    => MiscUtil::toFloatOrNull(MiscUtil::notEmptyOrNull($order, 'orderAmount')),
                'recipient'      => $recipient,
                'products'       => $products,
                'parcelPoint'    => $parcelPoint,
            );
        }

        return array('orders' => $result);
    }

    /**
     * Endpoint callback.
     *
     * @param int                   $orderId order id.
     * @param 'shipped'|'delivered' $route   tracking event.
     * @param object                $body    request body.
     *
     * @void
     */
    public function trackingEventHandler($orderId, $route, $body)
    {
        $boxtalconnect = boxtalconnect::getInstance();
        if (! is_object($body) || ! property_exists($body, 'accessKey') || $body->accessKey !== AuthUtil::getAccessKey(ShopUtil::$shopGroupId, ShopUtil::$shopId)) {
            ApiUtil::sendApiResponse(403);
        }

        if (! is_numeric($orderId)) {
            ApiUtil::sendApiResponse(400);
        }

        global $cookie;
        //phpcs:ignore
        $langId = $cookie->id_lang;
        $orderStatuses = OrderUtil::getOrderStatuses($langId);

        if ('shipped' === $route) {
            $orderShipped = ConfigurationUtil::get('BX_ORDER_SHIPPED');
            if ('' !== $orderShipped && null !== $orderShipped) {
                $isValidOrderShipped = false;
                foreach ($orderStatuses as $status) {
                    if ($status['id_order_state'] === $orderShipped) {
                        $isValidOrderShipped = true;
                    }
                }

                if (false === $isValidOrderShipped) {
                    ConfigurationUtil::set('BX_ORDER_SHIPPED', null);
                    NoticeController::addNotice(
                        NoticeController::$custom,
                        ShopUtil::$shopGroupId,
                        ShopUtil::$shopId,
                        array(
                            'status'  => 'warning',
                            'message' => $boxtalconnect->l('Boxtal connect: there\'s been a change in your order status list, we\'ve adapted your tracking event configuration. Please check that everything is in order.'),
                        )
                    );
                } else {
                    $order = new \Order((int) $orderId);
                    $order->setCurrentState($orderShipped);
                }
            }
        }

        if ('delivered' === $route) {
            $orderDelivered = ConfigurationUtil::get('BX_ORDER_DELIVERED');
            if ('' !== $orderDelivered && null !== $orderDelivered) {
                $isValidOrderDelivered = false;
                foreach ($orderStatuses as $status) {
                    if ($status['id_order_state'] === $orderDelivered) {
                        $isValidOrderDelivered = true;
                    }
                }

                if (false === $isValidOrderDelivered) {
                    ConfigurationUtil::set('BX_ORDER_DELIVERED', null);
                    NoticeController::addNotice(
                        NoticeController::$custom,
                        array(
                            'status'  => 'warning',
                            'message' => $boxtalconnect->l('Boxtal connect: there\'s been a change in your order status list, we\'ve adapted your tracking event configuration. Please check that everything is in order.'),
                        )
                    );
                } else {
                    $order = new \Order((int) $orderId);
                    $order->setCurrentState($orderDelivered);
                }
            }
        }

        ApiUtil::sendApiResponse(200);
    }
}
