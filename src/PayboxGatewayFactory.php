<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Triotech\SyliusPayboxBundle;

use Triotech\SyliusPayboxBundle\Action\AuthorizeAction;
use Triotech\SyliusPayboxBundle\Action\CancelAction;
use Triotech\SyliusPayboxBundle\Action\ConvertPaymentAction;
use Triotech\SyliusPayboxBundle\Action\CaptureAction;
use Triotech\SyliusPayboxBundle\Action\NotifyAction;
use Triotech\SyliusPayboxBundle\Action\RefundAction;
use Triotech\SyliusPayboxBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class PayboxGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name'           => 'paybox',
            'payum.factory_title'          => 'Paybox',
            'payum.action.capture'         => new CaptureAction(),
            'payum.action.authorize'       => new AuthorizeAction(),
            'payum.action.refund'          => new RefundAction(),
            'payum.action.cancel'          => new CancelAction(),
            'payum.action.notify'          => new NotifyAction(),
            'payum.action.status'          => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'site'          => '',
                'rang'          => '',
                'identifiant'   => '',
                'hmac'          => '',
                'hash'          => 'SHA512',
                'retour'        => 'Mt:M;Ref:R;Auto:A;Appel:T;Abo:B;Reponse:E;Transaction:S;Pays:Y;Signature:K',
                'sandbox'       => true,
                'type_paiement' => '',
                'type_carte'    => '',
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('site', 'rang', 'identifiant', 'hmac');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
