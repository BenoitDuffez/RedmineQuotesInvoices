<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var Quote
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
     * @var double
     *
     * @ORM\Column(name="percentage", type="decimal", precision=10, scale=2)
     */
    private $percentage = 100;

    /**
     * @var string
     *
     * @ORM\Column(name="replacement_text", type="text", nullable=true)
     */
    private $replacementText;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="text")
	 */
	private $title;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Section", inversedBy="invoices", cascade={"persist"})
	 * @ORM\JoinTable(name="invoice_section", joinColumns={@ORM\JoinColumn(name="invoice_id", referencedColumnName="id")},
	 *	 inverseJoinColumns={@ORM\JoinColumn(name="section_id", referencedColumnName="id")})
	 */
	private $sections;

	public function __construct() {
		$this->sections = new ArrayCollection();
	}

	public function updateTitle() {
		$this->setTitle(sprintf("%s%03d", $this->getQuote()->getTitle(), $this->getId()));
	}

	public function getTotal() {
		$total = 0;
		foreach ($this->getQuote()->getSections() as $section) {
			/* @var Section $section */
			foreach ($section->getItems() as $item) {
				/* @var Item $item */
				$total += $section->getRate() * $item->getHours();
			}
		}
		return $total;
	}

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

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return Invoice
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}

    /**
     * Add section
     *
     * @param Section $section
     *
     * @return Invoice
     */
    public function addSection(Section $section)
    {
		$section->addInvoice($this);
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Remove section
     *
     * @param Section $section
     */
    public function removeSection(Section $section)
    {
        $this->sections->removeElement($section);
    }

    /**
     * Get sections
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSections()
    {
        return $this->sections;
    }
}
