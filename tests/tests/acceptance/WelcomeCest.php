<?php

use WireMock\Client\WireMock;

class WelcomeCest
{
    public function _after(\AcceptanceTester $I): void
    {
        $I->cleanAllPreviousRequestsToWireMock();
    }

    public function tryToTest(\AcceptanceTester $I): void
    {
        $I->expectRequestToWireMock(
            WireMock::get(WireMock::urlEqualTo('/some/url'))
            ->willReturn(WireMock::aResponse()
            ->withHeader('Content-Type', 'text/plain')
            ->withBody('Hello world!'))
        );

        file_get_contents('http://localhost:18080/some/url');

        $I->receivedRequestToWireMock(
            WireMock::getRequestedFor(WireMock::urlEqualTo('/some/url'))
        );
    }
}
