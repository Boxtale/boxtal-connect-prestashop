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
* @author  Boxtal <api@boxtal.com>
*
* @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
*
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel">
  <div class="panel-heading">
    {l s='Congratulations, your Boxtal Connect plugin is installed ! You’re ready to grow your business through great shipping' mod='boxtalconnect'}
  </div>
  <div class="table-responsive-row clearfix">
      <div class="logo"></div>
    <p>{l s='First, [1]log in or sign up to a Boxtal account[/1] (it’s free !)'
      tags=["<a href=\"{$onboardingLink}\" target=\"_blank\">"]
      mod='boxtalconnect'}</p>
    <iframe
      id="tutorial-video"
      width="560"
      height="315"
      src="{l s='https://www.youtube.com/embed/ZNBQoTQX15w' mod='boxtalconnect'}"
      frameborder="0"
      allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen></iframe>

    <p>{l s='Then configure your shipping policy in Prestashop :' mod='boxtalconnect'}</p>
    <ul>
      <li>{l s='Create your shipping methods in Shipping > Carriers' mod='boxtalconnect'}</li>
      <li>{l s='If you want to display a pick-up location map for a giving method, link it to a parcel point map in Shipping > Boxtal Connect' mod='boxtalconnect'}</li>
      <li>{l s='Associate your order statuses to tracking events in the Boxtal Connect Menu' mod='boxtalconnect'}</li>
      <li>{l s='Copy the tracking URL by Boxtal and add it in your Shipping methods' mod='boxtalconnect'}</li>
    </ul>

    <p>{l s='You\'re ready to ship your first orders ! Happy shipping with Boxtal.' mod='boxtalconnect'}</p>
  </div>
</div>
