<?php

namespace App\Notifications;

use App\Models\Forecast\Values;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class HarmfulWeather extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly DateTimeInterface $hour, public readonly Values $values)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if ($notifiable->phone) {
            $channels[] = 'vonage';
        }

        return $channels;
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail'   => 'mail-queue',
            'vonage' => 'vonage-queue',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @throws Exception
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Potentially harmful weather')
            ->markdown('mail.harmful-weather', [
                'hour'   => $this->createTemplateHour($notifiable),
                'values' => $this->creteTemplateValues(),
            ]);
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @throws Exception
     */
    public function toVonage(object $notifiable): VonageMessage
    {
        $contents[] = 'Harmful weather';
        $contents[] = $this->createTemplateHour($notifiable)->format('d.m.Y H:i');

        $values = $this->creteTemplateValues();
        foreach ($values as $name => $value) {
            $contents[] = $name . ': ' . $value;
        }

        return (new VonageMessage)
            ->content(implode("\n", $contents));
    }

    /**
     * @throws Exception
     */
    private function createTemplateHour(object $notifiable): Carbon
    {
        return $this->hour->setTimezone(new DateTimeZone($notifiable->timezone ?? 'UTC'));
    }

    private function creteTemplateValues(): array
    {
        $harmfulPrecipitation = config('app.harmful_weather.precipitation_per_hour');
        $harmfulUVIndex       = config('app.harmful_weather.uv_index');

        $templateValues = [];

        if ($this->values->precipitation >= $harmfulPrecipitation) {
            $templateValues['Precipitation'] = $this->values->precipitation;
        }

        if ($this->values->uvIndex >= $harmfulUVIndex) {
            $templateValues['UV index'] = $this->values->uvIndex;
        }

        return $templateValues;
    }
}
