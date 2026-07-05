<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'fingerprint', 'exception_class', 'message', 'file', 'line', 'trace',
        'http_method', 'url', 'user_id', 'hotel_id', 'ip_address', 'user_agent',
        'request_data', 'status', 'resolution_notes', 'resolved_by', 'resolved_at',
        'occurrences', 'last_occurred_at',
    ];

    protected $casts = [
        'request_data'     => 'array',
        'resolved_at'      => 'datetime',
        'last_occurred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'resolved' => 'emerald',
            'ignored'  => 'slate',
            default    => 'rose',
        };
    }

    /**
     * Exceptions that represent expected/handled flow (validation, auth, 4xx)
     * rather than bugs, so they aren't worth tracking as system errors.
     */
    public static function isReportable(Throwable $e): bool
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return false;
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return false;
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return false;
        }

        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            return false;
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return false;
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            return false;
        }

        return true;
    }

    public static function recordFromThrowable(Throwable $e, ?Request $request = null): self
    {
        $fingerprint = hash('sha256', get_class($e).'|'.$e->getFile().'|'.$e->getLine());

        $existing = static::where('fingerprint', $fingerprint)->first();

        if ($existing) {
            $existing->increment('occurrences');
            $existing->forceFill(['last_occurred_at' => now()]);
            if (in_array($existing->status, ['resolved', 'ignored'], true)) {
                $existing->status = 'open';
            }
            $existing->save();

            return $existing;
        }

        return static::create([
            'code'             => static::generateCode(),
            'fingerprint'      => $fingerprint,
            'exception_class'  => get_class($e),
            'message'          => Str::limit((string) $e->getMessage(), 2000),
            'file'             => $e->getFile(),
            'line'             => $e->getLine(),
            'trace'            => $e->getTraceAsString(),
            'http_method'      => $request?->method(),
            'url'              => $request?->fullUrl(),
            'user_id'          => auth()->id(),
            'hotel_id'         => static::resolveHotelId($request),
            'ip_address'       => $request?->ip(),
            'user_agent'       => $request?->userAgent(),
            'request_data'     => static::sanitizeInput($request),
            'status'           => 'open',
            'occurrences'      => 1,
            'last_occurred_at' => now(),
        ]);
    }

    protected static function generateCode(): string
    {
        do {
            $code = 'ERR-'.strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    protected static function resolveHotelId(?Request $request): ?int
    {
        if (app()->bound('current_hotel')) {
            return app('current_hotel')?->id;
        }

        if ($request) {
            foreach ($request->route()?->parameters() ?? [] as $param) {
                if ($param instanceof Hotel) {
                    return $param->id;
                }
            }

            if ($slug = $request->session()->get('viewing_hotel')) {
                return Hotel::where('slug', $slug)->value('id');
            }
        }

        return null;
    }

    protected static function sanitizeInput(?Request $request): ?array
    {
        if (! $request) {
            return null;
        }

        $data = $request->except([
            'password', 'password_confirmation', 'current_password',
            'token', '_token', 'card_number', 'cvv',
        ]);

        return empty($data) ? null : $data;
    }
}
