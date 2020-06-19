# Sylius Paybox Bundle

Paybox gateway for Sylius projects.

## Usage

1. Install this bundle:

```bash
composer require triotech/sylius-paybox-bundle:dev-master@dev
```

2. Configure payment method in Sylius Admin Panel

## Complementary documentation

- [Sylius Payments](http://docs.sylius.org/en/latest/book/orders/payments.html)
- [Payum](https://github.com/Payum/Payum/blob/master/docs/index.md)
- [Paybox integration manual](http://www.paybox.com/wp-content/uploads/2017/08/ManuelIntegrationVerifone_PayboxSystem_V8.0_FR.pdf)

## Testing

**Testing configurations**
- https://www.paybox.com/espace-integrateur-documentation/la-plateforme-de-tests/
- https://www.paybox.com/espace-integrateur-documentation/comptes-de-tests/

**Merchant BO**
> https://preprod-admin.paybox.com/
> 199988832
> 1999888I

**Testing card**
- https://www.paybox.com/espace-integrateur-documentation/cartes-de-tests/
> **CB**
> 1111222233334444
> *any valid future date*
> 123

## History

This plugin has been forked from [triotech/sylius-paybox-bundle](https://git.triotech.fr/composer/sylius-paybox-bundle/)

> which was previously forked from [libre-informatique/sylius-paybox-bundle](https://github.com/sil-project/SyliusPayboxBundle)
> which was previously forked from [gdecorbiac/SyliusPayboxBundle](https://github.com/gdecorbiac/SyliusPayboxBundle)
> which was previously inspired by [remyma/payum-paybox](https://github.com/remyma/payum-paybox)