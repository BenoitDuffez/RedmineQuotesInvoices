<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Section
 *
 * @ORM\Table(name="section")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SectionRepository")
 */
class Section
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="rate", type="integer")
     */
    private $rate;

    /**
     * @var int
     *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quote", inversedBy="sections")
	 * @ORM\JoinColumn(name="quote_id", referencedColumnName="id")
     */
    private $quote;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\Item", mappedBy="section", cascade={"all"})
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	private $items;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="is_option", type="boolean")
	 */
	private $option;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="is_chosen", type="boolean")
	 */
	private $chosen;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Invoice", mappedBy="sections", cascade={"persist"})
	 */
	private $invoices;

	public function __construct() {
		$this->items = new ArrayCollection();
		$this->invoices = new ArrayCollection();
	}

	public function __clone() {
		if ($this->id) {
			$this->id = null;

			$itemsClone = new ArrayCollection();
			foreach ($this->items as $item) {
				$itemClone = clone $item;
				$itemClone->setSection($this);
				$itemsClone->add($itemClone);
			}
			$this->items = $itemsClone;
		}
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
     * Set title
     *
     * @param string $title
     *
     * @return Section
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Section
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set rate
     *
     * @param integer $rate
     *
     * @return Section
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set quote
     *
     * @param Quote $quote
     *
     * @return Section
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get quote
     *
     * @return int
     */
    public function getQuote()
    {
        return $this->quote;
    }

	/**
	 * @return mixed
	 */
	public function getItems() {
		return $this->items;
	}

	public function addItem(Item $item)
	{
		$item->setSection($this);
		$this->items->add($item);
		return $this;
	}

	public function removeItem(Item $item)
	{
		$this->items->removeElement($item);
		return $this;
	}

	public function __toString() {
		return "SECTION LOL";
	}

    /**
     * Set option
     *
     * @param boolean $option
     *
     * @return Section
     */
    public function setOption($option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get isOption
     *
     * @return boolean
     */
    public function isOption()
    {
        return $this->option;
    }

    /**
     * Set chosen
     *
     * @param boolean $chosen
     *
     * @return Section
     */
    public function setChosen($chosen)
    {
        $this->chosen = $chosen;

        return $this;
    }

    /**
     * Get chosen
     *
     * @return boolean
     */
    public function getChosen()
    {
        return $this->chosen;
    }

    /**
     * Add invoice
     *
     * @param Invoice $invoice
     *
     * @return Section
     */
    public function addInvoice(Invoice $invoice)
    {
		$invoice->addSection($this);
        $this->invoices[] = $invoice;

        return $this;
    }

    /**
     * Remove invoice
     *
     * @param Invoice $invoice
     */
    public function removeInvoice(Invoice $invoice)
    {
        $this->invoices->removeElement($invoice);
    }

    /**
     * Get invoices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

	/**
	 * Total number of hours of all items
	 * @return float
	 */
	public function getHours() {
		$total = 0;
		foreach ($this->getItems() as $item) {
			/* @var Item $item */
			$total += (double) $item->getHours();
		}
		return $total;
	}
}
