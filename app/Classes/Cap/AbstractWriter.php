<?php

namespace App\Classes\Cap;

abstract class AbstractWriter
{
    protected $document;

    protected $alert;

    public function __construct()
    {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;
    }

    /**
     * @param CapEntityInterface $alert
     */
    public function setAlertModel(CapEntityInterface $alert)
    {
        $this->alert = $alert;

        return $this;
    }

    /**
     * @param $path
     * @param string $type
     * @return $this
     */
    public function setXsl($path, $type = 'xsl')
    {
        if (! in_array($type, ['xsl', 'css'])) {
            throw new \InvalidArgumentException('Xsl "type" must be of type "xsl" or "css"');
        }

        $typeString = sprintf('type="text/%s" href="%s"', $type, $path);

        $xslt = $this->document->createProcessingInstruction('xml-stylesheet', $typeString);
        $this->document->appendChild($xslt);

        return $this;
    }
}
