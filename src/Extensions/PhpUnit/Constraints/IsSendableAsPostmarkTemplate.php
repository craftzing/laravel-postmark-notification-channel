<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Extensions\PhpUnit\Constraints;

use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplatesApi;
use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Constraint\Constraint;

use function json_encode;
use function method_exists;
use function sprintf;

use const JSON_PRETTY_PRINT;

final class IsSendableAsPostmarkTemplate extends Constraint
{
    private TemplatesApi $templatesApi;

    public function __construct(TemplatesApi $templatesApi)
    {
        $this->templatesApi = $templatesApi;
    }

    /**
     * {@inheritdoc}
     */
    public function matches($other): bool
    {
        $this->assertInstanceOfNotification($other);

        $message = $this->postmarkTemplateMessage($other);
        $validatedMessage = $this->templatesApi->validate($message);

        if (! $validatedMessage->isContentParseable()) {
            $this->fail($other, sprintf(
                'Postmark Template `%s` is not parseable. Make sure to fix the template markup on Postmark.',
                $message->identifier,
            ));
        }

        if ($validatedMessage->isInvalid()) {
            $this->fail($other, sprintf(
                "The provided notification message is invalid for Postmark Template `%s`.\n%s \n%s",
                $message->identifier,
                'MISSING: ' . json_encode($validatedMessage->missingVariables, JSON_PRETTY_PRINT),
                'INVALID: ' . json_encode($validatedMessage->invalidVariables, JSON_PRETTY_PRINT),
            ));
        }

        return true;
    }

    /**
     * @param mixed $other
     */
    private function assertInstanceOfNotification($other): void
    {
        if (! $other instanceof Notification) {
            $this->fail($other, sprintf(
                'Only instances of %s can be asserted to be sendable as Postmark Template.',
                Notification::class,
            ));
        }
    }

    /**
     * @param Notification $other
     */
    private function postmarkTemplateMessage($other): TemplateMessage
    {
        if (! method_exists($other, $method = 'toPostmarkTemplate')) {
            $this->fail($other, sprintf(
                '`%s()` method is missing for the notification to be sendable as Postmark Template.',
                $method,
            ));
        }

        return $other->toPostmarkTemplate();
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        return "{$this->exporter()->shortenedExport($other)} {$this->toString()}";
    }

    public function toString(): string
    {
        return 'is sendable as Postmark Template';
    }
}
