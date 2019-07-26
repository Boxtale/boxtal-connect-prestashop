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
*Z
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<section class="box">
    <h4>{l s='Pick-up location selected for the order' mod='boxtalconnect'}</h4>
    <address>
    <p>{$parcelpoint->name|escape:'html'}<br/>
    {$parcelpoint->address|escape:'html'}<br/>
    {$parcelpoint->zipcode|escape:'html'} {$parcelpoint->city|escape:'html'} {$parcelpoint->country|escape:'html'}</p>
    {if $hasOpeningHours}
    <h4>{l s='Opening hours' mod='boxtalconnect'}</h4>
<pre style="color: inherit; font-size: inherit; margin-top: 10px;">
{foreach $openingHours as $index => $openingHour}
{if $index % 2 === 1}<span style="background-color: #d8d8d8;">{/if}
{$openingHour|escape:'html'}
{if $index % 2 === 1}</span>{/if}
{/foreach}
</pre>
    {/if}
    </address>
</section>
