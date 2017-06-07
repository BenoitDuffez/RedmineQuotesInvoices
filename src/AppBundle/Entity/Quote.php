<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Redmine\Client;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Quote
 *
 * @ORM\Table(name="quote")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuoteRepository")
 */
class Quote
{
	/**
	 * Not a column. Some kind of singleton. Used to retrieve data from Redmine
	 * @var Client
	 */
	private $redmine;

	/**
	 * Not a column. Some kind of singleton. Retrieved from Redmine with the customer ID (including the custom fields)
	 * @var array
	 */
	private $customer;

	/**
	 * Not a column. Some kind of singleton. Retrieved from Redmine with the project ID
	 * @var array
	 */
	private $project;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_edition", type="datetime")
     */
    private $dateEdition;

    /**
     * @var string
     *
     * @ORM\Column(name="pdf_path", type="string", length=255)
     */
    private $pdfPath;

    /**
     * @var int
     *
	 * @Assert\NotEqualTo(value="0", message="Please select a customer")
	 * @Assert\NotBlank()
     * @ORM\Column(name="customer_id", type="integer")
     */
    private $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="project_id", type="integer")
     */
    private $projectId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\Section", mappedBy="quote", cascade={"all"})
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	private $sections;

	public function __construct() {
		$this->sections = new ArrayCollection();
		$this->redmine = null;
		$this->customer = null;
	}

	public function __clone() {
		if ($this->id) {
			$this->id = null;

			$sectionsClone = new ArrayCollection();
			foreach ($this->sections as $section) {
				$itemClone = clone $section;
				$itemClone->setQuote($this);
				$sectionsClone->add($itemClone);
			}
			$this->sections = $sectionsClone;
		}
	}

	/**
	 * Init/create the redmine client
	 * @param $url string Redmine URL
	 * @param $apiKey string Redmine API key
	 */
	public function initRedmine($url, $apiKey) {
		if ($this->redmine == null) {
			$this->redmine = new Client($url, $apiKey);
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
     * @return Quote
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Quote
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateEdition
     *
     * @param \DateTime $dateEdition
     *
     * @return Quote
     */
    public function setDateEdition($dateEdition)
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * Get dateEdition
     *
     * @return \DateTime
     */
    public function getDateEdition()
    {
        return $this->dateEdition;
    }

    /**
     * Set pdfPath
     *
     * @param string $pdfPath
     *
     * @return Quote
     */
    public function setPdfPath($pdfPath)
    {
        $this->pdfPath = $pdfPath;

        return $this;
    }

    /**
     * Get pdfPath
     *
     * @return string
     */
    public function getPdfPath()
    {
        return $this->pdfPath;
    }

    /**
     * Set customerId
     *
     * @param integer $customerId
     *
     * @return Quote
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

	/**
	 * Retrieve the customer from Redmine based on $this->customerId
	 * Asks for the custom fields too
	 * @return array
	 */
	private function getCustomer() {
		if ($this->customer == null) {
			$this->customer = $this->redmine->user->show($this->getCustomerId(), ['include' => ['custom_fields']]);
		}
		return $this->customer;
	}

	/**
	 * Retrieve the project from Redmine based on $this->projectId
	 * @return array
	 */
	private function getProject() {
		if ($this->project == null) {
			$this->project = $this->redmine->project->show($this->getProjectId());
		}
		return $this->project;
	}

	/**
	 * Retrieve one field (native or custom) from the customer
	 * @param $field
	 * @return string
	 */
	public function getCustomerField($field) {
		$customer = $this->getCustomer();
		if ($customer == null || !isset($customer['user'])) {
			return "";
		}
		if (isset($customer['user'][$field])) {
			return $customer['user'][$field];
		}
		if (!is_array($customer['user']['custom_fields'])) {
			return print_r($customer, true);
		}
		foreach ($customer['user']['custom_fields'] as $customField) {
			if ($customField['name'] == $field) {
				return $customField['value'];
			}
		}
		return "";
	}

	public function getProjectField($field) {
		$project = $this->getProject();
		if (is_array($project['project']) && isset($project['project'][$field])) {
			return $project['project'][$field];
		}
		return "";
	}

    /**
     * Set projectId
     *
     * @param integer $projectId
     *
     * @return Quote
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Quote
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

	/**
	 * @return ArrayCollection
	 */
	public function getSections() {
		return $this->sections;
	}
    
	public function addSection(Section $section)
	{
		$section->setQuote($this);
		$this->sections->add($section);
		return $this;
	}

	public function removeSection(Section $section)
	{
		$this->sections->removeElement($section);
		return $this;
	}

	public function updateTitle() {
		$this->setTitle(
			sprintf("%d%03d%03d%03d",
				date('Y'),
				$this->getCustomerId(),
				$this->getProjectId(),
				$this->getId()
			)
		);
		return $this;
	}
}

