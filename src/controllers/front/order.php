<?php
/**
 * Contains code for the order rest controller.
 */

use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalPrestashop\Util\ApiUtil;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\MiscUtil;
use Boxtal\BoxtalPrestashop\Util\OrderUtil;

/**
 * Order reset controller.
 *
 * Opens API endpoint to sync orders.
 */
class BoxtalOrderModuleFrontController extends ModuleFrontController
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
                    case RestClient::$PATCH:
                        $this->apiCallbackHandler();
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
    public function apiCallbackHandler()
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
            $items = OrderUtil::getItemsFromOrder((int) MiscUtil::notEmptyOrNull($order, 'id_order'));
            $products = array();
            foreach ($items as $item) {
                $product                = array();
                $product['weight']      = 0 !== (float) $item['product_weight'] ? (float) $item['product_weight'] : null;
                $product['quantity']    = (int) $item['product_quantity'];
                $product['price']       = (float) $item['product_price'];
                $product['description'] = $item['product_name'];
                $products[]             = $product;
            }

            $parcelPoint          = null;
            /*
                        $parcel_point_code     = Order_Util::get_meta($order, 'bw_parcel_point_code');
                        $parcel_point_operator = Order_Util::get_meta($order, 'bw_parcel_point_operator');
                        if ($parcel_point_code && $parcel_point_operator) {
                            $parcelPoint = array(
                                'code'     => $parcel_point_code,
                                'operator' => $parcel_point_operator,
                            );
                        }
            */
            $result[] = array(
                'id'      => MiscUtil::notEmptyOrNull($order, 'id_order'),
                'reference'      => MiscUtil::notEmptyOrNull($order, 'reference'),
                'status'         => MiscUtil::notEmptyOrNull($order, 'status'),
                'shippingMethod' => MiscUtil::notEmptyOrNull($order, 'shippingMethod'),
                'shippingAmount' => MiscUtil::toFloatOrNull(MiscUtil::notEmptyOrNull($order, 'shippingAmount')),
                'creationDate'   => MiscUtil::notEmptyOrNull($order, 'creationDate'),
                'orderAmount'    => MiscUtil::toFloatOrNull(MiscUtil::notEmptyOrNull($order, 'orderAmount')),
                'recipient'      => $recipient,
                'products'       => $products,
                'parcelPoint'    => $parcelPoint,
            );
        }

        return $result;
    }
}
