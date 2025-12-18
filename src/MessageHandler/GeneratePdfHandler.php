<?php

namespace App\MessageHandler;

use App\Message\GeneratePdfMessage;
use App\Repository\ProductRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GeneratePdfHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(GeneratePdfMessage $message): void
    {
        $id = $message->productId;

        $this->logger->info("WORKER: Started generating PDF for Product ID: $id");

        sleep(10);

        $product = $this->productRepository->find($id);
        if (!$product) {
            $this->logger->error("WORKER: Product $id not found!");
            return;
        }

        $this->logger->info(
            "WORKER: PDF Generated Successfully for " . $product->getName()
        );
    }
}
