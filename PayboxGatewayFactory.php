<?php

/*
 * This file is part of the Sil Project.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Sil\Bundle\SyliusPayboxBundle;

use Sil\Bundle\SyliusPayboxBundle\Action\AuthorizeAction;
use Sil\Bundle\SyliusPayboxBundle\Action\CancelAction;
use Sil\Bundle\SyliusPayboxBundle\Action\ConvertPaymentAction;
use Sil\Bundle\SyliusPayboxBundle\Action\CaptureAction;
use Sil\Bundle\SyliusPayboxBundle\Action\NotifyAction;
use Sil\Bundle\SyliusPayboxBundle\Action\RefundAction;
use Sil\Bundle\SyliusPayboxBundle\Action\StatusAction;
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
