<?php

use Beartropy\Saml2\Support\CertificateHelper;

test('clean removes PEM headers and whitespace', function () {
    $cert = "-----BEGIN CERTIFICATE-----\nMIICpDCCAYwCCQ\nabc123\n-----END CERTIFICATE-----";
    expect(CertificateHelper::clean($cert))->toBe('MIICpDCCAYwCCQabc123');
});

test('clean handles already-clean cert', function () {
    $cert = 'MIICpDCCAYwCCQabc123';
    expect(CertificateHelper::clean($cert))->toBe('MIICpDCCAYwCCQabc123');
});

test('clean strips spaces and newlines', function () {
    $cert = "  MIICp  DCCAYw\n\tCCQ  ";
    expect(CertificateHelper::clean($cert))->toBe('MIICpDCCAYwCCQ');
});
