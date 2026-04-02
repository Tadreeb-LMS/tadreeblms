<?php

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Spatie\Permission\Models\Permission;

if (!class_exists('LegacyFactoryBuilder')) {
    class LegacyFactoryBuilder
    {
        private string $modelClass;

        private ?int $count;

        private array $stateNames = [];

        public function __construct(string $modelClass, ?int $count = null)
        {
            $this->modelClass = $modelClass;
            $this->count = $count;
        }

        public function states($states): self
        {
            if (!is_array($states)) {
                $states = func_get_args();
            }

            foreach ($states as $state) {
                if (is_string($state) && $state !== '') {
                    $this->stateNames[] = $state;
                }
            }

            return $this;
        }

        public function state($state): self
        {
            return $this->states($state);
        }

        public function create(array $attributes = [])
        {
            if (($this->count ?? 1) > 1) {
                $items = [];
                for ($i = 0; $i < $this->count; $i++) {
                    $items[] = $this->createOne($attributes);
                }

                return new EloquentCollection($items);
            }

            return $this->createOne($attributes);
        }

        private function createOne(array $attributes)
        {
            $modelClass = $this->modelClass;
            $payload = array_merge(
                $this->defaultAttributes(),
                $this->stateAttributes(),
                $attributes
            );

            if ($this->hasState('softDeleted')) {
                unset($payload['deleted_at']);
            }

            $model = $modelClass::unguarded(function () use ($modelClass, $payload) {
                return $modelClass::query()->create($payload);
            });

            if ($this->hasState('softDeleted') && method_exists($model, 'delete')) {
                $model->delete();
            }

            return $model;
        }

        private function defaultAttributes(): array
        {
            switch ($this->modelClass) {
                case User::class:
                    return [
                        'first_name' => fake()->firstName(),
                        'last_name' => fake()->lastName(),
                        'email' => fake()->unique()->safeEmail(),
                        'password' => 'secret',
                        'confirmation_code' => md5(uniqid((string) mt_rand(), true)),
                        'active' => 1,
                        'confirmed' => 1,
                        'fav_lang' => 'english',
                    ];

                case Role::class:
                    return [
                        'name' => fake()->unique()->word(),
                        'guard_name' => config('auth.defaults.guard', 'web'),
                    ];

                case Permission::class:
                    return [
                        'name' => fake()->unique()->word(),
                        'guard_name' => config('auth.defaults.guard', 'web'),
                    ];

                default:
                    return [];
            }
        }

        private function stateAttributes(): array
        {
            $attributes = [];

            foreach ($this->stateNames as $stateName) {
                if ($this->modelClass === User::class) {
                    if ($stateName === 'active') {
                        $attributes['active'] = 1;
                    }

                    if ($stateName === 'inactive') {
                        $attributes['active'] = 0;
                    }

                    if ($stateName === 'confirmed') {
                        $attributes['confirmed'] = 1;
                    }

                    if ($stateName === 'unconfirmed') {
                        $attributes['confirmed'] = 0;
                    }

                    if ($stateName === 'softDeleted') {
                        $attributes['deleted_at'] = now();
                    }
                }
            }

            return $attributes;
        }

        private function hasState(string $state): bool
        {
            return in_array($state, $this->stateNames, true);
        }
    }
}

if (!function_exists('factory')) {
    function factory(string $modelClass, ?int $amount = null): LegacyFactoryBuilder
    {
        return new LegacyFactoryBuilder($modelClass, $amount);
    }
}
