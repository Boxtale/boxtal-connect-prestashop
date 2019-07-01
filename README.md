Welcome to the <a href="https://www.boxtal.com/">Boxtal Connect</a> for PrestaShop repository on GitHub. Here you can browse the source, look at open issues and keep track of development.

If you are not a developer, please use the Boxtal Connect plugin page on PrestaShop addons.

## Contributing to Boxtal Connect
If you have a patch or have stumbled upon an issue with our plugin, you can contribute this back to the code. Please read our [contributor guidelines](https://github.com/Boxtale/boxtal-connect-prestashop/blob/master/.github/CONTRIBUTING.md) for more information how you can do this.

## Default configuration for local use
Setting theses configurations into prestashop database (or copy paste this code in the begining of boxtalconnect.php) will make the module believe he is correctly paired, allowing access to configuration pages.

```
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
ConfigurationUtil::set('BX_ACCESS_KEY', 'aze');
ConfigurationUtil::set('BX_SECRET_KEY', 'aze');
ConfigurationUtil::set('BX_MAP_BOOTSTRAP_URL', 'url');
ConfigurationUtil::set('BX_MAP_TOKEN_URL', 'token');
ConfigurationUtil::set('BX_MAP_LOGO_IMAGE_URL', 'image');
ConfigurationUtil::set('BX_MAP_LOGO_HREF_URL', 'href');
ConfigurationUtil::set('BX_PP_NETWORKS', serialize(array(
    "CHRP_NETWORK" => array("Chronopost"),
    "SOGP_NETWORK" =>  array("Relais Colis"),
    "UPSE_NETWORK" => array("UPS"),
    "MONR_NETWORK" => array("Mondial Relay", "Happy Post", "Punto Pack", "Boxtal Mondial Relay")
)));
ConfigurationUtil::set('BX_TRACKING_URL_PATTERN', 'tracking');
```

Note that the communication with sellershop service will not work with this configuration, use this for local development only.
