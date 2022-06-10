<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\User;
use Symfony\Component\Uid\Ulid;

class UserRepository extends AbstractRepository
{
    public function auth(): ?User
    {
        $response = $this->api->get('users/auth.json')->toArray();

        return $this->api->as($response, User::class);
    }

    public function new(User $user): User
    {
        $response = $this->api->post('users.json', [
            'json' => $user,
        ])->toArray();

        return $this->api->as($response, User::class);
    }

    public function list(array $options = []): array
    {
        $response = $this->api->get('users.json', $options)->toArray();

        return $this->api->asArray($response, User::class);
    }

    public function search(string $terms, array $options = []): array
    {
        $options = array_merge($options, [
            'query' => [
                'q' => $terms,
            ],
        ]);

        $users = $this->api->get('users/search.json', $options)->toArray();

        return $this->api->asArray($users, User::class);
    }


    public function byId(Ulid|string $user): ?User
    {
        $response = $this->api->get(sprintf(
            'users/%s.json',
            $user,
        ))->toArray();

        return $this->api->as($response, User::class);
    }

    public function byUsername(string $username): ?User
    {
        $response = $this->api->get('users.json', [
            'query' => ['username' => $username],
        ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->byId($response['id']);
    }

    public function recoverPassword(Ulid|string $user): User
    {
        $response = $this->api->get(sprintf(
            'users/%s/recover.json',
            $user,
        ))->toArray();

        return $this->api->as($response, User::class);
    }

    /**
     * Has returning status code for specific statuses for user
     * recovery, useful for validators.
     *
     * 204 Recovery is still possible.
     * 400 Invalid recovery token.
     * 403 Recovery token has expired.
     * 404 User not found.
     */
    public function recoverPasswordCheckStatus(Ulid|string $user, string $token): User
    {
        $response = $this->api->get(sprintf(
            'users/%s/recover/%s.json',
            $user,
            $token,
        ))->toArray();

        return $this->api->as($response, User::class);
    }

    public function recoverUpdatePassword(Ulid|string $user, string $token, string $plainPassword): User
    {
        $data = [
            'id' => $user,
            'plainPassword' => $plainPassword,
        ];

        $response = $this->api->patch(sprintf(
            'users/%s/recover/%s.json',
            (string)$user,
            $token,
        ), ['json' => $data])->toArray();

        return $this->api->as($response, User::class);
    }
}
