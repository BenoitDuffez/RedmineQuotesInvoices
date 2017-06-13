<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoice
 *
 * @ORM\Table(name="invoice")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvoiceRepository")
 */
class Invoice
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quote", inversedBy="invoices")
	 * @ORM\JoinColumn(name="quote_id", referencedColumnName="id")
     */
    private $quote;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="billing_date", type="datetime")
     */
    private $billingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="percentage", type="decimal", precision=10, scale=2)
     */
    private $percentage;

    /**
     * @var string
     *
     * @ORM\Column(name="replacement_text", type="text", nullable=true)
     */
    private $replacementText;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set quote
     *
     * @param Quote $quote
     *
     * @return Invoice
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set billingDate
     *
     * @param \DateTime $billingDate
     *
     * @return Invoice
     */
    public function setBillingDate($billingDate)
    {
        $this->billingDate = $billingDate;

        return $this;
    }

    /**
     * Get billingDate
     *
     * @return \DateTime
     */
    public function getBillingDate()
    {
        return $this->billingDate;
    }

    /**
     * Set percentage
     *
     * @param string $percentage
     *
     * @return Invoice
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage
     *
     * @return string
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set replacementText
     *
     * @param string $replacementText
     *
     * @return Invoice
     */
    public function setReplacementText($replacementText)
    {
        $this->replacementText = $replacementText;

        return $this;
    }

    /**
     * Get replacementText
     *
     * @return string
     */
    public function getReplacementText()
    {
        return $this->replacementText;
    }
}

