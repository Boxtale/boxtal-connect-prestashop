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
  <div class="panel-heading">
    <i class="icon-truck"></i> {l s='Boxtal Connect' mod='boxtalconnect'}
  </div>
  <ul class="nav nav-tabs" id="boxtal-tab">
    <li>
      <a href="#boxtal-parcelpoint"{if $showParcelPoint} class="has-info"{/if}>
        <i class="icon-archive"></i>
        {l s='Pick-up point' mod='boxtalconnect'}
        <span class="badge">{$parcelpointBadge|escape:'html'}</span>
      </a>
    </li>
    <li>
      <a href="#boxtal-tracking"{if $showTracking} class="has-info"{/if}>
        <i class="icon-truck "></i>
        {l s='Tracking' mod='boxtalconnect'}
        <span class="badge">{$trackingBadge|escape:'html'}</span>
      </a>
    </li>
  </ul>
  <div class="tab-content panel">
    <div class="tab-pane" id="boxtal-parcelpoint">
      {if $showParcelPoint}
        {if !$parcelpointValidForCarrier}
        <div class="alert alert-warning" role="alert">
          <p class="alert-text">
            {if $carrierHasNetworks}
              {l s='The pick-up point network (%s) do not match the order\'s shipping method networks : %s' sprintf=[$parcelpointShippingMethods, $carrierShippingMethods] mod='boxtalconnect'}
            {else}
              {l s='The order\'s shipping method do not accept any pick-up point network.' mod='boxtalconnect'}
            {/if}
          </p>
        </div>
        {/if}
        <p>{l s='Your client chose a pick-up point with the code [1]%s[/1] from %s.' sprintf=[$parcelpoint->code, $parcelpointShippingMethods] tags=['<b>'] mod='boxtalconnect'}</p>
        {if $showParcelPointAddress}
            <h4>{l s='Pick-up point address :' mod='boxtalconnect'}</h4>
            <p>
              {$parcelpoint->name|escape:'html'}<br/>
              {$parcelpoint->address|escape:'html'}<br/>
              {$parcelpoint->zipcode|escape:'html'} {$parcelpoint->city|escape:'html'} {$parcelpoint->country|escape:'html'}
            </p>
            {if $hasOpeningHours}
            <h4>{l s='Opening hours' mod='boxtalconnect'}</h4>
            <pre style="color: inherit; font-size: inherit; margin-top: 10px;background-color: inherit; border: 0; padding: 0"
                >{$openingHours}</pre>
            {/if}
        {/if}
      {else}
        <div class="list-empty hidden-print">
            <div class="list-empty-msg">
              <i class="icon-warning-sign list-empty-icon"></i>
              {l s='No pick-up point for this order.' mod='boxtalconnect'}
          </div>
        </div>
      {/if}
    </div>
    <div class="tab-pane bx-tracking" id="boxtal-tracking">
      {if $showTracking}
        {if $tracking->shipmentsTracking|@count == 1}
          <p>{l s='Your order has been sent in 1 shipment.' mod='boxtalconnect'}</p>
        {else}
          <p>{l s='Your order has been sent in %s shipment.' sprintf=[$tracking->shipmentsTracking|@count] mod='boxtalconnect'}</p>
        {/if}

        {foreach from=$tracking->shipmentsTracking item=shipment}
          <h4>{l s='Shipment reference %s' sprintf=[$shipment->reference] mod='boxtalconnect'}</h4>
          {assign var="parcelCount" value=$shipment->parcelsTracking|@count}
          {if $parcelCount == 1 || $parcelCount == 0}
            <p>{l s='Your shipment has %s package.' sprintf=[$parcelCount] mod='boxtalconnect'}</p>
          {else}
            <p>{l s='Your shipment has %s packages.' sprintf=[$parcelCount] mod='boxtalconnect'}</p>
          {/if}

          {foreach from=$shipment->parcelsTracking item=parcel}
            {if $parcel->trackingUrl !== null}
              <p>{l s='Package reference [1]%s[/1]' sprintf=[$parcel->reference] tags=['<a href="'|cat:$parcel->trackingUrl|cat:'" target="_blank">'] mod='boxtalconnect'}</p>
            {else}
              <p>{l s='Package reference %s' sprintf=[$parcel->reference] mod='boxtalconnect'}</p>
            {/if}

            {if $parcel->trackingEvents|is_array & $parcel->trackingEvents|@count gt 0}
              {foreach from=$parcel->trackingEvents item=event}
                <p>
                  {$event->date|date_format:$dateFormat} {$event->message|escape:'html'}
                </p>
              {/foreach}
            {else}
              {l s='No tracking event for this package yet.' mod='boxtalconnect'}
            {/if}
            <br/>
           {/foreach}

        {/foreach}
      {else}
        <div class="list-empty hidden-print">
            <div class="list-empty-msg">
              <i class="icon-warning-sign list-empty-icon"></i>
              {l s='No tracking yet.' mod='boxtalconnect'}
          </div>
        </div>
      {/if}
    </div>
  </div>
  <script>
    $('#boxtal-tab li a').click(function (e) {
      e.preventDefault()
      $(this).tab('show');
    })
    $("#boxtal-tab li a").first().click();
    $("#boxtal-tab li a.has-info").first().click();
  </script>

  <div class="bx-tracking">
  </div>
</div>
