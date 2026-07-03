<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActivityLogService
{
    /** @var list<string> */
    private array $hidden = [
        'password',
        'remember_token',
    ];

    public function logModelEvent(string $event, Model $subject, array $changes = []): void
    {
        if ($event === 'updated' && $this->onlyIgnorableChanges($changes)) {
            return;
        }

        $original = $subject->getOriginal();
        $properties = match ($event) {
            'created' => ['attributes' => $this->filterAttributes($subject->getAttributes())],
            'updated' => [
                'old'     => $this->filterAttributes(Arr::only($original, array_keys($changes))),
                'changes' => $this->filterAttributes($changes),
            ],
            'deleted' => ['attributes' => $this->filterAttributes($subject->getAttributes())],
            default   => [],
        };

        $this->store(
            event: $event,
            description: $this->describeModelEvent($event, $subject),
            subject: $subject,
            properties: $properties,
        );
    }

    public function logAuth(string $event, Authenticatable $user, string $guard): void
    {
        $name = $user->name ?? $user->email ?? ('#' . $user->getKey());

        $this->store(
            event: $event,
            description: sprintf('%s %s on guard %s', class_basename($user), $event, $guard),
            subject: $user instanceof Model ? $user : null,
            properties: ['guard' => $guard, 'name' => $name],
            causer: $user instanceof Model ? $user : null,
        );
    }

    private function store(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?Model $causer = null,
    ): void {
        [$causerType, $causerId] = $this->resolveCauser($causer);

        $entry = [
            'event'        => $event,
            'description'  => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id'   => $subject?->getKey(),
            'causer_type'  => $causerType,
            'causer_id'    => $causerId,
            'properties'   => $properties ?: null,
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
            'logged_at'    => now()->toIso8601String(),
        ];

        $this->writeToFile($entry);

        ActivityLog::create(Arr::except($entry, ['logged_at']));
    }

    /** @param array<string, mixed> $entry */
    private function writeToFile(array $entry): void
    {
        Log::channel('useractivity')->info($entry['description'], $entry);
    }

    /** @return array{0: ?string, 1: ?int} */
    private function resolveCauser(?Model $explicit = null): array
    {
        if ($explicit) {
            return [$explicit->getMorphClass(), $explicit->getKey()];
        }

        foreach (['admin', 'employee', 'api_admin', 'api_employee'] as $guard) {
            $user = Auth::guard($guard)->user();

            if ($user instanceof Model) {
                return [$user->getMorphClass(), $user->getKey()];
            }
        }

        return [null, null];
    }

    private function describeModelEvent(string $event, Model $subject): string
    {
        $type  = Str::headline(class_basename($subject));
        $label = $this->resolveLabel($subject);

        return trim(sprintf(
            '%s %s %s',
            $type,
            $event,
            $label ? "({$label})" : '#' . $subject->getKey()
        ));
    }

    private function resolveLabel(Model $model): ?string
    {
        if (filled($model->getAttribute('name'))) {
            return (string) $model->getAttribute('name');
        }

        if (filled($model->getAttribute('employee_id'))) {
            return (string) $model->getAttribute('employee_id');
        }

        if (filled($model->getAttribute('email'))) {
            return (string) $model->getAttribute('email');
        }

        return null;
    }

    /** @param array<string, mixed> $attributes */
    private function filterAttributes(array $attributes): array
    {
        return collect($attributes)
            ->except($this->hidden)
            ->map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('c') : $value)
            ->all();
    }

    /** @param array<string, mixed> $changes */
    private function onlyIgnorableChanges(array $changes): bool
    {
        return empty(array_diff(array_keys($changes), ['updated_at', 'last_login_at']));
    }
}
