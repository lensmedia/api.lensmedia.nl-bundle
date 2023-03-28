<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class UserRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('users.json', $options)->toArray();

        return $this->api->asArray($response, User::class);
    }

    public function get(User|Ulid|string $user, array $options = []): ?User
    {
        $response = $this->api->get(sprintf(
            'users/%s.json',
            $user->id ?? $user,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), User::class);
    }

    public function post(User $user, array $options = []): User
    {
        $response = $this->api->post('users.json', [
            'json' => $user,
        ] + $options)->toArray();

        return $this->api->as($response, User::class);
    }

    public function patch(User $user, array $options = []): User
    {
        $url = sprintf('users/%s.json', $user->id);

        $response = $this->api->patch($url, [
            'json' => $user,
        ] + $options)->toArray();

        return $this->api->as($response, User::class);
    }

    public function delete(User|Ulid|string $user, array $options = []): void
    {
        $url = sprintf('users/%s.json', $user->id ?? $user);

        $this->api->delete($url, $options)->getHeaders();
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

    /**
     * @deprecated Use `UserRepository::get` instead.
     */
    public function byId(Ulid|string $user): ?User
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::get" instead.', __METHOD__, __CLASS__);

        return $this->get($user);
    }

    public function getByUsername(string $username): ?User
    {
        $response = $this->api->get('users.json', [
            'query' => ['username' => $username],
        ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->get($response['id']);
    }

    /**
     * @deprecated Use `UserRepository::getByUsername` instead.
     */
    public function byUsername(string $username): ?User
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::getByUsername" instead.', __METHOD__, __CLASS__);

        return $this->getByUsername($username);
    }

    public function auth(string $username, string $password, array $options = []): ?User
    {
        $response = $this->api->get('users/auth.json', array_merge_recursive($options, [
            'auth_basic' => [$username, $password],
        ]))->toArray();

        return $this->api->as($response, User::class);
    }

    public function recoverPassword(User|Ulid|string $user): User
    {
        $response = $this->api->get(sprintf(
            'users/%s/recover.json',
            $user->id ?? $user,
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
    public function recoverPasswordCheckStatus(User|Ulid|string $user, string $token): int
    {
        return $this->api->get(sprintf(
            'users/%s/recover/%s.json',
            $user->id ?? $user,
            $token,
        ))->getStatusCode();
    }

    public function recoverUpdatePassword(User|Ulid|string $user, string $token, string $plainPassword): User
    {
        $data = [
            'id' => $user->id ?? $user,
            'plainPassword' => $plainPassword,
        ];

        $response = $this->api->patch(sprintf(
            'users/%s/recover/%s.json',
            $user->id ?? $user,
            $token,
        ), ['json' => $data])->toArray();

        return $this->api->as($response, User::class);
    }
}
