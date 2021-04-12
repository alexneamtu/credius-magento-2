# Credius for Magento 2.x

#  About

  * Personal loans via credius.ro for Magento 2.x.

#  Requirements

  You're going to need the Partner Code, API Key and Public Key from [Credius](https://www.credius.ro/).
  

#  Installation

## Manual installation

Upload files to your Magento 2.x installation, inside the app/code/Credius/PaymentGateway folder.

## Composer installation

Inside the Magento 2.x root run `composer require crediuspay/payment-gateway`

## Activation of the module

Run the following after the installation:
```bash
    bin/magento module:enable Credius_PaymentGateway --clear-static-content
 
    bin/magento setup:upgrade
    
    bin/magento setup:di:compile
 
    bin/magento setup:static-content:deploy -f
 
    bin/magento cache:clean 
```

## Setup

- Go to your Magento dashboard. 

    -> Stores     
    -> Configuration     
    -> Sales    
    -> Payment Methods


- Find Credius & setup the plugin using the API Key provided by Credius and the Callback Url as `<domain>/credius/webhook/receiver`

#  Support

##  Credius Support

  * Have questions? Please visit [Credius](https://www.credius.ro/).

##  Magento Support
    
  * [Homepage](http://magento.com/)
  * [Support](http://magento.com/help/overview)
