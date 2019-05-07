{**
* 2007-2019 PrestaShop
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
*
* @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
*
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel">
    <div class="table-responsive-row clearfix">
      <p>{l s='Then configure your shipping policy in Prestashop :' mod='boxtalconnect'}</p>
      <ul>
          <li>{l s='Create your shipping methods in Shipping > Carriers' mod='boxtalconnect'}</li>
          <li>{l s='Link it to a parcel point map in Shipping > Boxtal Connect' mod='boxtalconnect'}</li>
          <li>{l s='Associate your order statuses to tracking events' mod='boxtalconnect'}</li>
          <li>{l s='Copy the tracking URL by Boxtal and add it in your Shipping methods' mod='boxtalconnect'}</li>
      </ul>
      {if null !== $helpCenterUrl}
          <p>{l s='You can find illustrated step by step guides in our [1]help center[/1]'
              tags=["<a href=\"{$helpCenterUrl}\" target=\"_blank\">"]
              mod='boxtalconnect'}</p>
      {/if}
    </div>
</div>
<div class="panel">
  <form method="POST">
    <div class="panel-heading">
      {l s='Parcel point map display' mod='boxtalconnect'}
    </div>
    <div class="table-responsive-row clearfix">
      <p>{l s='Activate a parcel point network on a shipping method in order to display a parcel point map for this carrier.' mod='boxtalconnect'}</p>
      <table class="table">
        <thead>
        <th>{l s='ID' mod='boxtalconnect'}</th>
        <th>{l s='Name' mod='boxtalconnect'}</th>
        <th>{l s='Logo' mod='boxtalconnect'}</th>
        {foreach from=$parcelPointNetworks key=k item=network}
          <th>{', '|implode:$network}</th>
        {/foreach}
        </thead>
        <tbody>
        {foreach from=$carriers key=c item=carrier}
          <tr>
            <td>{$carrier.id_carrier|escape:'htmlall':'UTF-8'}</td>
            <td>{$carrier.name|escape:'htmlall':'UTF-8'}</td>
            <td>
              {if isset($carrier.logo)}
                <img class="imgm img-thumbnail" src="{$carrier.logo|escape:'htmlall':'UTF-8'}">
              {/if}
            </td>
            {foreach from=$parcelPointNetworks key=k item=network}
              <td><input type="checkbox" name="parcelPointNetworks_{$carrier.id_carrier|escape:'htmlall':'UTF-8'}[]" value="{$k|escape:'htmlall':'UTF-8'}"
                  {if false !== $carrier.parcel_point_networks && in_array($k, $carrier.parcel_point_networks)}
                    checked
                  {/if}
                ></td>
            {/foreach}
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right" name="submitParcelPointNetworks">
        <i class="process-icon-save"></i>{l s='Save' mod='boxtalconnect'}
      </button>
    </div>
  </form>
</div>

<div class="panel">
  <form method="POST">
    <div class="panel-heading">
      {l s='Statuses associated to tracking events' mod='boxtalconnect'}
    </div>
    <div class="table-responsive-row clearfix">
      <p>{l s='Associate your order statuses to tracking events.' mod='boxtalconnect'}</p>
      <table class="table">
        <thead>
          <th>{l s='Tracking event' mod='boxtalconnect'}</th>
          <th>{l s='Associated order status' mod='boxtalconnect'}</th>
        </thead>
        <tbody>
          <tr>
            <td>{l s='Order prepared' mod='boxtalconnect'}</td>
            <td>
              <select name="orderPrepared">
                <option value="" {if null === $orderPrepared}selected{/if}>{l s='No status associated' mod='boxtalconnect'}</option>
                {foreach from=$orderStatuses key=k item=status}
                  <option value="{$status.id_order_state|escape:'htmlall':'UTF-8'}" {if $status.id_order_state === $orderPrepared}selected{/if}>{$status.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
              </select>
            </td>
          </tr>
          <tr>
            <td>{l s='Order shipped' mod='boxtalconnect'}</td>
            <td>
              <select name="orderShipped">
                <option value="" {if null === $orderShipped}selected{/if}>{l s='No status associated' mod='boxtalconnect'}</option>
                {foreach from=$orderStatuses key=k item=status}
                  <option value="{$status.id_order_state|escape:'htmlall':'UTF-8'}" {if $status.id_order_state === $orderShipped}selected{/if}>{$status.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
              </select>
          </td>
          </tr>
          <tr>
            <td>{l s='Order delivered' mod='boxtalconnect'}</td>
            <td>
              <select name="orderDelivered">
                <option value="" {if null === $orderDelivered}selected{/if}>{l s='No status associated' mod='boxtalconnect'}</option>
                {foreach from=$orderStatuses key=k item=status}
                  <option value="{$status.id_order_state|escape:'htmlall':'UTF-8'}" {if $status.id_order_state === $orderDelivered}selected{/if}>{$status.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right" name="submitTrackingEvents">
        <i class="process-icon-save"></i>{l s='Save' mod='boxtalconnect'}
      </button>
    </div>
  </form>
</div>

<div class="panel">
  <div class="panel-heading">
    {l s='Tracking url' mod='boxtalconnect'}
  </div>
  <div class="row">
    <p>{l s='If you wish to display tracking for your shipments sent with Boxtal, here is the tracking url to add to your shipment methods:' mod='boxtalconnect'}</p>
    <p class="well">{$trackingUrlPattern|escape:'htmlall':'UTF-8'}</p>
    <p>{l s="Your PrestaShop order ID will be set as tracking reference when the carrier you've chosen with Boxtal has picked up your shipment." mod='boxtalconnect'}</p>
  </div>
</div>
