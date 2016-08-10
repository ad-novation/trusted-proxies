<?php

namespace spec\MobileCashout\TrustedProxies;

use PhpSpec\ObjectBehavior;

class ProxyManagerSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(__DIR__.'/test_data.php');
    }

    public function it_trusts_stored_range()
    {
        $this->isTrusted('3.3.3.50')->shouldBe(true);
    }

    public function it_does_not_trust_outside_range()
    {
        $this->isTrusted('3.3.4.0')->shouldBe(false);
    }

    public function it_returns_true_for_valid_ipv4()
    {
        $this->isIpv4('1.2.3.4')->shouldBe(true);
    }

    public function it_returns_false_for_invalid_ipv4()
    {
        $this->isIpv4('probably invalid')->shouldBe(false);
    }

    public function it_returns_true_for_valid_ipv6()
    {
        $this->isIpv6('2001:cdba:0000:0000:0000:0000:3257:965')->shouldBe(true);
    }

    public function it_returns_false_for_invalid_ipv6()
    {
        $this->isIpv6('probably invalid')->shouldBe(false);
    }
}
