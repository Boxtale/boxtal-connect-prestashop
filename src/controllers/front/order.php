<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Boxtal <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

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
        $entityBody = Tools::file_get_contents('php://input');

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

            $phone = MiscUtil::notEmptyOrNull($order, 'phone_mobile') === null ? MiscUtil::notEmptyOrNull($order, 'phone') : MiscUtil::notEmptyOrNull($order, 'phone_mobile');
            $recipient = array(
                'firstname' => MiscUtil::notEmptyOrNull($order, 'firstname'),
                'lastname' => MiscUtil::notEmptyOrNull($order, 'lastname'),
                'company' => MiscUtil::notEmptyOrNull($order, 'company'),
                'addressLine1' => MiscUtil::notEmptyOrNull($order, 'address1'),
                'addressLine2' => MiscUtil::notEmptyOrNull($order, 'address2'),
                'city' => MiscUtil::notEmptyOrNull($order, 'city'),
                'state' => MiscUtil::notEmptyOrNull($order, 'state_iso'),
                'postcode' => MiscUtil::notEmptyOrNull($order, 'postcode'),
                'country' => MiscUtil::notEmptyOrNull($order, 'country_iso'),
                'phone' => $phone,
                'email' => MiscUtil::notEmptyOrNull($order, 'email'),
            );
            $items = OrderUtil::getItemsFromOrder($orderId);
            $products = array();
            foreach ($items as $item) {
                $product = array();
                $product['weight'] = 0 !== (float) $item['product_weight'] ? (float) $item['product_weight'] : null;
                $product['quantity'] = (int) $item['product_quantity'];
                $product['price'] = (float) $item['product_price'];
                $description = ProductUtil::getProductDescriptionMultilingual((int) $item['product_id']);
                $product['description'] = $description;
                $products[] = $product;
            }

            $parcelPoint = null;
            $parcelPointCode = OrderStorageUtil::get($orderId, 'bxParcelPointCode');
            $parcelPointNetwork = OrderStorageUtil::get($orderId, 'bxParcelPointNetwork');
            if ($parcelPointCode && $parcelPointNetwork) {
                $parcelPoint = array(
                    'code' => $parcelPointCode,
                    'network' => $parcelPointNetwork,
                );
            }

            $multilingualStatus = OrderUtil::getStatusMultilingual($orderId);
            $multilingualShippingMethod = array();
            $shippingMethodName = MiscUtil::notEmptyOrNull($order, 'shippingMethod');
            foreach (\Language::getLanguages(true) as $lang) {
                $multilingualShippingMethod[Tools::strtolower(str_replace('-', '_', $lang['language_code']))] = $shippingMethodName;
            }

            $result[] = array(
                'internalReference' => $orderId,
                'reference' => MiscUtil::notEmptyOrNull($order, 'reference'),
                'status' => array(
                    'key' => OrderUtil::getStatusId($orderId),
                    'translations' => $multilingualStatus,
                ),
                'shippingMethod' => array(
                    'key' => OrderUtil::getCarrierReference($orderId),
                    'translations' => $multilingualShippingMethod,
                ),
                'shippingAmount' => MiscUtil::toFloatOrNull(MiscUtil::notEmptyOrNull($order, 'shippingAmount')),
                'creationDate' => MiscUtil::dateW3Cformat(MiscUtil::notEmptyOrNull($order, 'creationDate')),
                'orderAmount' => MiscUtil::toFloatOrNull(MiscUtil::notEmptyOrNull($order, 'orderAmount')),
                'recipient' => $recipient,
                'products' => $products,
                'parcelPoint' => $parcelPoint,
            );
        }

        return array('orders' => $result);
    }

    /**
     * Endpoint callback.
     *
     * @param int $orderId order id
     * @param 'shipped'|'delivered' $route tracking event
     * @param object $body request body
     *
     * @void
     */
    public function trackingEventHandler($orderId, $route, $body)
    {
        $boxtalconnect = boxtalconnect::getInstance();
        if (!is_object($body) || !property_exists($body, 'accessKey') || $body->accessKey !== AuthUtil::getAccessKey(ShopUtil::$shopGroupId, ShopUtil::$shopId)) {
            //ApiUtil::sendApiResponse(403);
        }

        if (!is_numeric($orderId)) {
            ApiUtil::sendApiResponse(400);
        }

        //phpcs:ignore
        $langId = \Context::getContext()->language->id;
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
                            'status' => 'warning',
                            'message' => $boxtalconnect->l('Boxtal connect: there\'s been a change in your order status list, we\'ve adapted your tracking event configuration. Please check that everything is in order.'),
                        )
                    );
                } else {
                    $order = new \Order((int) $orderId);
                    $order->setCurrentState($orderShipped);

                    $carrierId = OrderUtil::getCarrierId($orderId);
                    if (null !== $carrierId) {
                        $url = ShippingMethodUtil::getCarrierTrackingUrl($carrierId);
                        if (null !== $url && str_replace('@', '%s', $url) === ConfigurationUtil::getTrackingUrlPattern()) {
                            \Db::getInstance()->update(
                                'orders',
                                array('shipping_number' => pSQL($orderId)),
                                'id_order = ' . (int) $orderId
                            );

                            \Db::getInstance()->update(
                                'order_carrier',
                                array('tracking_number' => pSQL($orderId)),
                                'id_order = ' . (int) $orderId
                            );
                        }
                    }
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
                        ShopUtil::$shopGroupId,
                        ShopUtil::$shopId,
                        array(
                            'status' => 'warning',
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
