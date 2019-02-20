<?php
/**
 * @category  Perico
 * @package   MagentoChallenge
 * @version   1.0.0
 * @author    Diego Perico <perico.oliveiran@gmail.com>
 */
namespace Perico\MagentoChallenge\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Review\Model\Review;
use Magento\Framework\Console\Cli;

class ProductReviews extends Command
{
    /**
     * Review product collection factory
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     */
    protected $productReviewCollection;

    /**
     * @param string|null $name
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productReviewCollectionFactory
     */
    public function __construct(
        $name = null,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productReviewCollectionFactory
    ){
        $this->productReviewCollection = $productReviewCollectionFactory;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("perico:product:reviews");
        $this->setDescription('Show Product Reviews');
        $this->addArgument('product_id', InputArgument::REQUIRED, __('Please, enter the product id'));
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $productId = $input->getArgument('product_id');

        if (!empty($productId) && is_numeric($productId)) {
            try {
                $productReviewCollection = $this->productReviewCollection->create();
                $productReviewCollection->addFieldToFilter('entity_id', $productId);

                if ($productReviewCollection->getSize() > 0) {
                    $output->writeln("Product Id: {$productId}\n");
                    $productReviews = $productReviewCollection->getItems();

                    foreach ($productReviews as $productReview) {
                        $output->writeln("Review Status Id: {$productReview->getStatusId()}");
                        $status = $this->getReviewProductStatus($productReview->getStatusId());
                        $output->writeln("Review Status: {$status}");
                        $output->writeln("Review Date: {$productReview->getCreatedAt()}");
                        $output->writeln("Review Detail: {$productReview->getDetail()}");
                        $output->writeln("Customer Nickname: {$productReview->getNickname()}\n");
                    }
                    return Cli::RETURN_SUCCESS;
                }

                $output->writeln("This product does not have any reviews yet");
                return Cli::RETURN_SUCCESS;
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                return Cli::RETURN_FAILURE;
            }
        }

        $output->writeln("The product Id must be a numeric value");
        return Cli::RETURN_FAILURE;
    }

    /**
     * Get product review status description
     *
     * @param int $statusId
     * @return string
     */
    protected function getReviewProductStatus($statusId)
    {
        $status = '';
        switch ($statusId) {
            case Review::STATUS_APPROVED:
                $status =  'Approved';
                break;
            case Review::STATUS_PENDING:
                $status =  'Pending';
                break;
            case Review::STATUS_NOT_APPROVED:
                $status =  'Not approved';
                // no break
        }
        return $status;
    }
}