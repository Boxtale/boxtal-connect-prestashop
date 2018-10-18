<div class="panel">
  <div class="panel-heading">
    <i class="icon-truck"></i> {l s='Boxtal tracking' mod='boxtalconnect'}
  </div>
  <div class="bx-tracking">
    {if isset($tracking->shipmentsTracking) & !empty($tracking->shipmentsTracking)}

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
                {$event->date|date_format:$dateFormat} {$event->message}
              </p>
            {/foreach}
          {else}
            {l s='No tracking event for this package yet.' mod='boxtalconnect'}
          {/if}
          <br/>
         {/foreach}

      {/foreach}
    {/if}
  </div>
</div>
