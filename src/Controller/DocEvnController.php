<?php

namespace App\Controller;

use App\Entity\Product;
use App\Message\GeneratePdfMessage;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class DocEvnController extends AbstractController
{
    #[Route('/create', name: 'create_product', methods: ['POST'])]
    public function createProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ): JsonResponse {

        $data = $request->toArray();

        $product = new Product();

        $product->setName($data['name']);
        $product->setPrice($data['price']);

        $entityManager->persist($product);
        $entityManager->flush();

        $bus->dispatch(new GeneratePdfMessage($product->getId()));

        return $this->json(
            [
            'message' => 'Product created successfully!',
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    #[Route('/update/{id}', name: 'product_update', methods: ['Put'])]
    public function updateProduct(
        int $id,
        Request $request,
        ProductRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $product = $repository->find($id);

        if (!$product) {
            return $this->json(
                ['error' => 'Product not found'],
                404
            );
        }

        $data = $request->toArray();

        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }

        $entityManager->flush();

        return $this->json(
            [
            'message' => 'Product updated!',
            'id' => $product->getId(),
            'created_at' => $product->getCreatedAt()->format('H:i:s'),
            'updated_at' => $product->getUpdatedAt()->format('H:i:s'),
            ]
        );
    }

    #[Route('/audit/{id}', name: 'test_audit', methods: ['POST'])]
    public function Audit(
        int $id,
        Request $request,
        ProductRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {

        $product = $repo->find($id);
        $data = $request->toArray();

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }

        $em->flush();

        return $this->json(
            ['message' => 'Check your logs!']
        );
    }

    #[Route('/delete/{id}', name: 'test_delete', methods: ['DELETE'])]
    public function deleteProduct(
        int $id,
        ProductRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $product = $repository->find($id);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        $entityManager->remove($product);

        // $entityManager->flush();

        return $this->json(
            [
            'message' => 'Delete request processed',
            'id' => $product->getId(),
            'deleted_at' => $product->getDeletedAt()?->format('Y-m-d H:i:s')
            ]
        );
    }
    #[Route('/find-softdeleted-product/{id}', name: 'test_find', methods: ['GET'])]
    public function findProduct(
        int $id,
        ProductRepository $repository
    ): JsonResponse {

        $product = $repository->find($id);

        if (!$product) {
            return $this->json(
                ['message' => 'Product not found'],
                404
            );
        }

        return $this->json(
            ['name' => $product->getName()]
        );
    }
}
