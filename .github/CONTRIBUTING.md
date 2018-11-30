# Contributing

Community made patches, bug reports and contributions are welcome on this project.

When contributing please ensure you follow the guidelines below to help us stay on top of things.

__Please Note:__

GitHub is for _bug reports and contributions only_ - if you have a support question or a request for a customization this is not the right place to post it.

## Reporting issues

If something isn't working, congratulations you've found a bug! Help us fix it by submitting an issue report:

* Make sure you have a [GitHub account](https://github.com/signup/free)
* Search the [Existing Issues](https://github.com/Boxtale/boxtal-connect-prestashop/issues) to be sure that the one you've noticed isn't already there
* Submit a report for your issue
  * Clearly describe the issue (including steps to reproduce it if it's a bug)
  * Make sure you fill in the earliest version that you know has the issue
  * Give us your PHP and PrestaShop versions
  * Be sure to mention any plugin (with version) which you have installed and you suspect might be related to the issue

## Submitting pull requests
WooCommerce
### Coding guidelines

#### Plugin base structure

Our plugin base structure implements as closely as possible the [Plugin Container pattern](https://www.smashingmagazine.com/2015/05/how-to-use-autoloading-and-a-plugin-container-in-wordpress-plugins/), for better code maintainability and modularity.

#### Standards

Our plugin follows PHP and [PrestaShop](https://devdocs.prestashop.com/1.7/development/coding-standards/) coding standards. Optionally it's possible to install [EditorConfig](http://editorconfig.org/) on your editor to auto configure with indentation, line endings and other settings.

As a rule, we will consider a PHP code sniffing green light as a sign your code is compliant with the standards.

### Local work environment

#### Building an image

Your first step is to set up your local work environment. Luckily we have included tools to easily generate a working PrestaShop site with everything you need to start modifying our plugin.
* Install [docker](https://www.docker.com/)
* Clear your 80 port. For instance, if you have a local apache running on port 80, you should stop it
* Clone this repository, then command prompt into it
* Install PHP, or better yet multiple php versions support (when building with composer, your php version should match le php version of the image you are building)
* Install [composer](https://getcomposer.org/)
* Run `composer install`
* Install [node and npm](https://docs.npmjs.com/getting-started/installing-node#installing-npm-from-the-nodejs-site)
* Run `npm install`
* Build your own docker image with this command: `./factory/docker/build.sh <php_version> <prestashop_version> <multisite>` (use 0 or 1 for the multisite parameter). If you don't want to spend time debugging the build process, we strongly recommend that you stay inside the versions tested in the `.travis.yml` file
* Run the image with: 
`./factory/docker/run.sh <php_version> <prestashop_version> <multisite>` (use 0 or 1 for the multisite parameter)
* if you run the image with some environment variables, you can also set the Boxtal environment to our test environment: APIURL=https://api.boxtal.build, ONBOARDINGURL=https://www.boxtal.build/onboarding

That's it! A fully configured PrestaShop site will be running on http://localhost. The Boxtal Connect plugin will be pre-installed.

Admin login/pwd will be admin/admin

Mysql login/pwd will be dbadmin/dbpass

__Please Note:__

The running container will be named "boxtal_connect_woocommerce", so if you want to run the command again to reset your container you should remove the container beforehand (`docker stop boxtal_connect_woocommerce && docker rm boxtal_connect_woocommerce`).

#### Syncing code

Building an image each time you change the plugin code is a nice feature, but it's rather time consuming. So there's a command to sync your code with your running container. Still inside the repo folder, run: `./factory/docker/sync.sh`

#### Testing

Before you can run unit tests, you need to build them: `./factory/docker/test/build.sh <prestashop_version> <multisite>` (use 0 or 1 for the multisite parameter)

##### PHP unit tests

To run unit tests, run: `./factory/docker/test/run-unit.sh`

#### Code sniffing

To run code sniffing, run: `./factory/common/phpcs.sh`

### Submitting a pull request

* Fork the repository on GitHub
* When committing, reference your issue number (#1234) and include a note about the fix
* Push the changes to your fork, this will trigger a build in travis
* Make sure the travis build does not fail, that means code sniffing and unit tests must all be greenlit
* Submit a pull request on the master branch of the Boxtal Connect repository

After you follow the step above, the next stage will be waiting on us to merge your Pull Request. We review them all, and make suggestions and changes as and if necessary.





