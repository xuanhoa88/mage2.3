<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Comment model
 *
 * @method \Magefan\Blog\Model\ResourceModel\Comment _getResource()
 * @method \Magefan\Blog\Model\ResourceModel\Comment getResource()
 * @method int getPostId()
 * @method $this setPostId(int $value)
 * @method int getCustomerId()
 * @method $this setCustomerId(int $value)
 * @method int getAdminId()
 * @method $this setAdminId(int $value)
 * @method int getParentId()
 * @method $this setParentId(int $value)
 * @method int getStatus()
 * @method $this setStatus(int $value)
 * @method int getAuthorType()
 * @method $this setAuthorType(int $value)
 * @method string getAuthorNickname()
 * @method $this setAuthorNickname(string $value)
 * @method string getAuthorEmail()
 * @method $this setAuthorEmail(string $value)
 * @method string getText()
 * @method $this setText(string $value)
 * @method string getCreationTime()
 * @method $this setCreationTime(string $value)
 * @method string getUpdateTime()
 * @method $this setUpdateTime(string $value)
 */
class Comment extends AbstractModel
{
    /**
     * @var \Magefan\Blog\Model\AuthorFactory
     */
    protected $postFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $commentCollectionFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $author;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Comment\Collection
     */
    protected $comments;

    /**
     * Initialize dependencies.
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magefan\Blog\Model\PostFactory                              $postFactory
     * @param \Magento\Customer\Model\CustomerFactory                      $customerFactory,
     * @param \Magento\User\Model\Useractory                               $userFactory,
     * @param \Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory  $commentCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->postFactory = $postFactory;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magefan\Blog\Model\ResourceModel\Comment');
    }

    /**
     * Retrieve model title
     * @param  boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Comments' : 'Comment';
    }

    /**
     * Retrieve true if post is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getStatus() == \Magefan\Blog\Model\Config\Source\CommentStatus::APPROVED);
    }

    /**
     * Retrieve post
     * @return \Magefan\Blog\Model\Post | false
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post', false);
            if ($postId = $this->getData('post_id')) {
                $post = $this->postFactory->create()->load($postId);
                if ($post->getId()) {
                    $this->setData('post', $post);
                }
            }
        }

        return $this->getData('post');
    }

    /**
     * Retrieve author
     * @return \\Magento\Framework\DataObject
     */
    public function getAuthor()
    {
        if (null === $this->author) {
            $this->author = new \Magento\Framework\DataObject;
            $this->author->setType(
                $this->getAuthorType()
            );

            switch ($this->getAuthorType()) {
                case \Magefan\Blog\Model\Config\Source\AuthorType::GUEST:
                    $this->author->setData([
                        'nickname' => $this->getAuthorNickname(),
                        'email' => $this->getAuthorEmail(),
                    ]);
                    break;
                case \Magefan\Blog\Model\Config\Source\AuthorType::CUSTOMER:
                    $customer = $this->customerFactory->create();
                    $customer->load($this->getCustomerId());
                    $this->author->setData([
                        'nickname' => $customer->getName(),
                        'email' => $this->getEmail(),
                        'customer' => $customer,
                    ]);
                    break;
                case \Magefan\Blog\Model\Config\Source\AuthorType::ADMIN:
                    $admin = $this->userFactory->create();
                    $admin->load($this->getAdminId());
                    $this->author->setData([
                        'nickname' => $customer->getName(),
                        'email' => $this->getEmail(),
                        'admin' => $admin,
                    ]);
                    break;
            }
        }

        return $this->author;
    }

    /**
     * Retrieve parent comment
     * @return self || false
     */
    public function getParentComment()
    {
        $k = 'parent_comment';
        if (null === $this->getData($k)) {
            $this->setData($k, false);
            if ($pId = $this->getParentId()) {
                $comment = clone $this;
                $comment->load($pId);
                if ($comment->getId()) {
                    $this->setData($k, $comment);
                }
            }
        }

        return $this->getData($k);
    }

    /**
     * Retrieve child comments
     * @return \Magefan\Blog\Model\ResourceModel\Comment\Collection
     */
    public function getChildComments()
    {
        if (is_null($this->comments)) {
            $this->comments = $this->commentCollectionFactory->create()
                ->addFieldToFilter('parent_id', $this->getId());
        }

        return $this->comments;
    }

    /**
     * Retrieve true if comment is reply to other comment
     * @return boolean
     */
    public function isReply()
    {
        return (bool)$this->getParentId();
    }

    /**
     * Save the comment
     * @return this
     */
    public function save()
    {
        $this->validate();
        return parent::save();
    }

    /**
     * Validate comment
     * @return void
     */
    public function validate()
    {
        if (mb_strlen($this->getText()) < 3) {
            throw new \Exception(__('Comment text is too short.'), 1);
        }
    }

    /**
     * Retrieve post publish date using format
     * @param  string $format
     * @return string
     */
    public function getPublishDate($format = 'Y-m-d H:i:s')
    {
        return \Magefan\Blog\Helper\Data::getTranslatedDate(
            $format,
            $this->getData('creation_time')
        );
    }
}
