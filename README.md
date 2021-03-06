domains-whmcs4
==============

### Namecheap.com WHMCS Registrar Plugin for WHMCS 4

***

Note: We have two variants of Namecheap.com WHMCS Registrar plugins for WHMCS version 4 and 5. You need to download and install Namecheap.com WHMCS Registrar Plugin version 1.1.12 is for WHMCS version 4 and Namecheap.com WHMCS Registrar Plugin version 1.2.7 for WHMCS 5.

##### Updated on Jul 25, 2012 to Version 1.1.12 for WHMCS 4

- Extended attributes for .me.uk domains

##### ReleaseNotes

Namecheap.com WHMCS Registrar plug-in is an open-source plug-in that is distributed free of charge. It focuses on integrating Namecheap as a domain registrar at WHMCS.

After the integration you can setup Namecheap as the default registrar for your customers and decide which services and TLDs to offer to your customers from within the WHMCS admin area.

##### Pre-requisites

- Access to WHMCS admin area.
- An understanding of Namecheap’s environments.
- Namecheap account with API access enabled on the desired environment.

NOTE: Namecheap has a production as well as a test server environment. The test server environment is called Sandbox. We urge you to test the WHMCS Registrar plug-in in our sandbox environment, before pointing it to production. For more detailed information, please visit the developer's site at http://www.namecheap.com/support/api/api.aspx

##### Download and Installation

- If you are a github user, fork/clone this repository into your github account. If you do not have a github account, use the download ZIP option to make a copy.
- Create a folder called namecheap under Modules/Registrar in your WHMCS root directory and paste the downloaded namecheap.php and namecheapapi.php files inside the folder (the namecheap.php and namecheapapi.php files are located inside the downloaded archive). The plug-in installation is complete.

##### Configuration

To configure WHMCS for use with Namecheap, perform the following steps:

1. Login to your **WHMCS admin** panel.
2. Click on **Setup** menu, select **Products/Services** and click on **Domain Registrars**.
3. Click on Activate next to Namecheap in the list:
 ![Activate Plugin](http://files.namecheap.com/images/googlecode/activate.png "Activate Plugin")
4. Enter your API credentials. If you wish to try out the plug-in in sandbox, make sure to enter your sandbox username, sandbox API key in the corresponding text boxes and check the “Test Mode” checkbox:
5. Optional settings:
 - If you have a promotional coupon code from Namecheap you may enter it in the module settings. The discounted price will be automatically applied on your orders according to the coupon pricing.
 - If you’re having any issues with the module it is recommended to enable DebugMode and check the logs under Utilities > Logs > Module Log. If this option is disabled the module will be logging only errors returned by the module.
6. Click Save Changes.


That’s it. The Namecheap plug-in is now ready for use and will function just like any other built-in WHMCS registrar module. You can now make Namecheap as the automatic registrar, configure TLDs and services for all your customers. To perform these actions, click on the Setup menu, select Products/Services and click on Domain Pricing in your WHMCS admin panel:

You can refer to http://docs.whmcs.com/Domains_Configuration for more information.

To add additional fields required for our module (language for IDN domains, Job Title field for .CA and .AU domains, extended attributed for .FR domains) you will need to connect our own additionaldomainfields.php file. To do this, please add the following strings in the end of includes/additionaldomainfields.php file:

 `if (file_exists(dirname(FILE)."/../modules/registrars/namecheap/additionaldomainfields.php")) {
  include dirname(FILE)."/../modules/registrars/namecheap/additionaldomainfields.php"; 
 }`


##### Support

PPlease [submit a ticket](https://support.namecheap.com/index.php?/Tickets/Submit) to report bugs, provide feedback or receive assistance.
