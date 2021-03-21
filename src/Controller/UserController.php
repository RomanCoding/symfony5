<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(Request $request): JsonResponse
    {
        if ($request->get('search')) {
            $users = $this->getDoctrine()->getRepository(User::class)
                ->findByUsernameOrEmail($request->get('search'), ['id' => 'DESC'])
                ->toArray();
        } else {
            $users = $this->getDoctrine()->getRepository(User::class)
                ->findBy([], ['id' => 'DESC']);
        }

        $result = array_map(function ($user) {
            return $this->userJsonResponse($user);
        }, $users);

        return $this->json($result);
    }

    /**
     * @Route("/user/{id}", name="user_show", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'No id parameter'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($user === null) {
            return $this->json(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($this->userJsonResponse($user));
    }

    /**
     * @Route("/user", name="user_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, UserService $userService)
    {
        try {
            $user = $userService->create(
                $request->get('username'),
                $request->get('email'),
                $request->get('password')
            );
        } catch (\InvalidArgumentException|DBALException $exception) {
            return $this->json(['error' => 'Validation failed'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            return $this->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($this->userJsonResponse($user));
    }

    /**
     * @Route("/user/{id}", name="user_update", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, int $id, UserService $userService)
    {
        if (empty($id)) {
            return $this->json(['error' => 'No id parameter'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($user === null) {
            return $this->json(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $userService->update(
                $user,
                $request->get('username'),
                $request->get('email')
            );
        } catch (\InvalidArgumentException|DBALException $exception) {
            return $this->json(['error' => 'Validation failed'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            return $this->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($this->userJsonResponse($user));
    }

    /**
     * @Route("/user/{id}", name="user_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id)
    {
        if (empty($id)) {
            return $this->json(['error' => 'No id parameter'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($user === null) {
            return $this->json(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($user);
        $manager->flush();

        return $this->json([]);
    }

    private function userJsonResponse(User $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ];
    }
}
