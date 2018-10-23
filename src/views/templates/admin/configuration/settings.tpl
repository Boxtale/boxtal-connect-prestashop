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
