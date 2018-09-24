<div class="panel">
  <form method="POST">
    <div class="panel-heading">
      {l s='Parcel point map display' mod='boxtal'}
    </div>
    <div class="table-responsive-row clearfix">
      <p>{l s='Activate a parcel point operator on a carrier in order to display a parcel point map for this carrier.' mod='boxtal'}</p>
      <table class="table">
        <thead>
        <th>{l s='ID' mod='boxtal'}</th>
        <th>{l s='Name' mod='boxtal'}</th>
        <th>{l s='Logo' mod='boxtal'}</th>
        {foreach from=$parcelPointOperators key=k item=operator}
          <th>{$operator.label|escape:'htmlall':'UTF-8'}</th>
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
            {foreach from=$parcelPointOperators key=k item=operator}
              <td><input type="checkbox" name="parcelPointOperators_{$carrier.id_carrier|escape:'htmlall':'UTF-8'}[]" value="{$operator.code|escape:'htmlall':'UTF-8'}"
                  {if false !== $carrier.parcel_point_operators && in_array($operator.code, $carrier.parcel_point_operators)}
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
      <button type="submit" class="btn btn-default pull-right" name="submitParcelPointOperators">
        <i class="process-icon-save"></i>{l s='Save' mod='boxtal'}
      </button>
    </div>
  </form>
</div>
