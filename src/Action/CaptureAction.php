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

namespace Triotech\SyliusPayboxBundle\Action;

use Triotech\SyliusPayboxBundle\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\ApiAwareTrait;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction implements GatewayAwareInterface, ApiAwareInterface, ActionInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->query['error_code'])) {
            $model->replace($httpRequest->query);
        } else {
            $this->api->doPayment((array) $model);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
