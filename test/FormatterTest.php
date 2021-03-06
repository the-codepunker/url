<?php

namespace League\Url\Test;

use League\Url\Formatter;
use League\Url\Url;
use PHPUnit_Framework_TestCase;

/**
 * @group url
 */
class FormatterTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = Url::createFromUrl(
            'http://login:pass@gwóźdź.pl:443/test/query.php?kingkong=toto&foo=bar+baz#doc3'
        );
    }

    public function testFormatHostAscii()
    {
        $formatter = new Formatter;
        $this->assertSame(Formatter::HOST_UNICODE, $formatter->getHostEncoding());
        $formatter->setHostEncoding(Formatter::HOST_ASCII);
        $this->assertSame(Formatter::HOST_ASCII, $formatter->getHostEncoding());
        $this->assertSame('xn--gwd-hna98db.pl', $formatter->format($this->url->getHost()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalifHostEncoding()
    {
        (new Formatter())->setHostEncoding('toto');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalifQueryEncoding()
    {
        (new Formatter())->setQueryEncoding('toto');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalifQuerySeparator()
    {
        (new Formatter())->setQuerySeparator(new \StdClass);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidFormat()
    {
        $formatter = new Formatter;
        $formatter->format(new \StdClass);
    }

    public function testFormatHostUnicode()
    {
        $formatter = new Formatter;
        $formatter->setHostEncoding(Formatter::HOST_UNICODE);
        $this->assertSame('gwóźdź.pl', $formatter->format($this->url->getHost()));
    }

    public function testFormatQueryRFC1738()
    {
        $formatter = new Formatter;
        $this->assertSame(Formatter::QUERY_RFC3986, $formatter->getQueryEncoding());
        $formatter->setQueryEncoding(Formatter::QUERY_RFC1738);
        $this->assertSame(Formatter::QUERY_RFC1738, $formatter->getQueryEncoding());
        $this->assertSame('kingkong=toto&foo=bar+baz', $formatter->format($this->url->getQuery()));
    }

    public function testFormatQueryRFC3986()
    {
        $formatter = new Formatter;
        $formatter->setQueryEncoding(Formatter::QUERY_RFC3986);
        $this->assertSame('kingkong=toto&foo=bar%20baz', $formatter->format($this->url->getQuery()));
    }

    public function testFormatQueryWithSeparator()
    {
        $formatter = new Formatter;
        $this->assertSame('&', $formatter->getQuerySeparator());
        $formatter->setQuerySeparator('&amp;');
        $this->assertSame('&amp;', $formatter->getQuerySeparator());
        $this->assertSame('kingkong=toto&amp;foo=bar%20baz', $formatter->formatQuery($this->url->getQuery()));
    }

    public function testFormatURL()
    {
        $formatter = new Formatter;
        $formatter->setQuerySeparator('&amp;');
        $formatter->setHostEncoding(Formatter::HOST_ASCII);
        $expected = 'http://login:pass@xn--gwd-hna98db.pl:443/test/query.php?kingkong=toto&amp;foo=bar%20baz#doc3';
        $this->assertSame($expected, $formatter->format($this->url));
    }
}
