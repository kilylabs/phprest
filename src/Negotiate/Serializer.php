<?php namespace Phrest\Negotiate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Negotiation\FormatNegotiator;
use Phrest\Exception\Exception;

trait Serializer
{
    /**
     * @param mixed $value
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function serialize($value, Request $request, Response $response)
    {
        $request = $this->getNegotiatedRequest($request);

        if (in_array($request->attributes->get('_mime_type'), [Mime::JSON, Mime::XML])) {
            $response->setContent(
                $this->serviceSerializer()->serialize(
                    $value,
                    $request->attributes->get('_format')
                )
            );
        } else {
            $response->setContent(
                $this->serviceHateoas()->serialize(
                    $value,
                    $request->attributes->get('_format')
                )
            );
        }

        $response->headers->set(
            'Content-Type',
            $request->attributes->get('_mime_type')
        );

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Request
     *
     * @throws Exception
     */
    protected function getNegotiatedRequest(Request $request)
    {
        /** @var Request $clonedRequest */
        $clonedRequest = clone $request;

        $negotiator = new FormatNegotiator();
        $negotiator->registerFormat('json', [Mime::HAL_JSON, Mime::JSON], true);
        $negotiator->registerFormat('xml', [Mime::HAL_XML, Mime::XML], true);

        if ( ! in_array(
            $negotiator->getBest($clonedRequest->headers->get('accept'))->getValue(),
            [Mime::HAL_JSON, Mime::HAL_XML, Mime::JSON, Mime::XML, '*/*'])
        ) {
            throw new Exception('Not supported format', 0, 412);
        }

        $clonedRequest->attributes->set(
            '_format',
            $negotiator->getBestFormat(
                $clonedRequest->headers->get('accept'),
                ['json', 'xml']
            )
        );
        $clonedRequest->attributes->set(
            '_mime_type',
            $negotiator->getBest(
                $clonedRequest->headers->get('accept'),
                [Mime::JSON, Mime::XML, Mime::HAL_JSON, Mime::HAL_XML]
            )->getValue()
        );

        return $clonedRequest;
    }

    /**
     * @return \JMS\Serializer\Serializer
     */
    abstract public function serviceSerializer();

    /**
     * @return \Hateoas\Hateoas
     */
    abstract public function serviceHateoas();
}
