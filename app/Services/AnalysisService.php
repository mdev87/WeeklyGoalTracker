<?php

namespace App\Services;

use App\Data\Analysis\AnalysisData;
use App\Data\Analysis\CompareItem;
use App\Data\Analysis\InfoItem;
use App\Data\Dashboard\WeeklyStatsData;
use App\Models\User;
use App\Repositories\TimeLogRepository;
use App\Repositories\WeekRepository;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class AnalysisService
{
    public function __construct(
        private WeeklyStatsService $weekStatsService,
        private WeekRepository $weekRepository,
        private TimeLogRepository $timeLogRepository
    ) {}

    public function analyze(User $user): AnalysisData
    {
        $weekStats = $this->weekStatsService->getStats($user);

        $currentWeek = $this->weekRepository->getCurrentWeek(
            $user,
            config('week.default_planned_minutes')
        );

        return new AnalysisData(
            weekSummary: $this->getWeekSummary($user),
            date: Jalalian::fromFormat(
                'Y-m-d',
                $currentWeek->week_start_date
            )->format('%B، %Y'),

            strongestGoal: $this->strongestGoal($weekStats),
            weakestGoal: $this->weakestGoal($weekStats),
            weekOffer: $this->weekOffer($weekStats),
            weeklyProgress: $this->weeklyProgress($user),

            compares: $this->compares($user)
        );
    }

    private function getWeekSummary(User $user)
    {
        return Cache::remember(
            "week-summary:{$user->id}:".jdate()->getFirstDayOfWeek()->format('Y-m-d'),
            now()->addHours(12),
            function () use ($user) {
                try {
                    $weekStats = $this->weekStatsService->getStats($user);

                    $response = Prism::text()
                        ->using(Provider::OpenRouter, 'openrouter/free')
                        ->withProviderOptions([
                            'temperature' => 0.3,
                        ])
                        ->withSystemPrompt(<<<'Prompt'
            تو یک دستیار فارسی‌زبان برای اپلیکیشن مدیریت اهداف هفتگی هستی.

            کاربر اطلاعات عملکرد هفته خود را به صورت JSON به تو می‌دهد.

            فقط بر اساس اطلاعات موجود در JSON یک خلاصه کوتاه از عملکرد هفته بنویس.

            قوانین بسیار مهم:

            - پاسخ فقط و فقط به زبان فارسی باشد.
            - پاسخ حداکثر ۴ جمله باشد.
            - پاسخ بین ۴۰ تا ۹۰ کلمه باشد.
            - از Markdown، تیتر، لیست، بولت، شماره‌گذاری، ایموجی و نقل قول استفاده نکن.
            - فقط متن نهایی را برگردان.
            - اعداد را با رقم فارسی بنویس.
            - نام هدف‌ها را دقیقاً از داده‌ها استفاده کن.
            - درباره رنگ هدف‌ها یا شناسه اهداف صحبت نکن.
            - درباره فیلدهایی که خالی هستند (مثل todayLogs) اظهار نظر نکن.
            - اطلاعاتی که در JSON وجود ندارد را حدس نزن.
            - اگر وضعیت کاربر خوب بود، فقط عملکرد او را تحسین کن و متن را تمام کن.
            - اگر وضعیت متوسط بود، فقط یک پیشنهاد کوتاه و عملی بده.
            - اگر وضعیت ضعیف بود، کاربر را به ادامه مسیر تشویق کن و حداکثر یک پیشنهاد عملی ارائه بده.
            - فقط در صورتی پیشنهاد عملی بده که واقعاً نقطه ضعف مشخصی وجود داشته باشد؛ در غیر این صورت فقط خلاصه عملکرد را بنویس.
            - حداکثر یک پیشنهاد ارائه بده.
            - پیشنهاد باید کوتاه و کاملاً عملی باشد.
            - از عبارت‌هایی مثل «بر اساس داده‌ها»، «تحلیل نشان می‌دهد» و مشابه آن استفاده نکن.
            - نام هدف‌ها را دقیقاً همان‌طور که در JSON آمده استفاده کن و هرگز آن‌ها را خلاصه، ترجمه یا تغییر نده.
            - متن باید کاملاً طبیعی و شبیه نوشته یک فارسی‌زبان باشد.
            - لحن پاسخ باید شبیه پیام کوتاهی باشد که در صفحه اصلی یک اپلیکیشن نمایش داده می‌شود، نه گزارش، مقاله یا متن ترجمه‌شده. از جمله‌های کوتاه و محاوره‌ای استفاده کن.
            - از ترجمه تحت‌اللفظی و عبارت‌های غیرطبیعی مانند «کاهش تنش بین اهداف»، «بهبود تعادل تمام»، «به کار گرفتن زمان»، «قدم موثری به جلو بردی» و موارد مشابه استفاده نکن.
            - اگر پیشنهادی ارائه می‌دهی، آن را فقط بر اساس هدفی که کمترین پیشرفت را داشته و بیشترین زمان برنامه‌ریزی‌شده را دارد بنویس.
            - اگر پیشنهادی لازم نیست، متن را بدون پیشنهاد تمام کن.
            - اگر درباره درصد یا نام هدف مطمئن نیستی، آن را ذکر نکن. فقط از اطلاعاتی استفاده کن که مستقیماً در JSON وجود دارد.

            نمونه لحن مطلوب:
            این هفته عملکرد خوبی داشتی. «ورزش» را تا ۸۰٪ پیش بردی که نتیجه قابل توجهی است. در مقابل، «مطالعه کتاب» هنوز پیشرفت کمی داشته و بهتر است هفته آینده زمان ثابتی برای آن در نظر بگیری.

            مثال ۱ (عملکرد عالی):
            این هفته عالی عمل کردی. «ورزش» را تا ۹۳٪ پیش بردی و بیشتر برنامه هفتگی‌ات را انجام دادی. همین روند را حفظ کن.

            مثال ۲ (عملکرد متوسط):
            این هفته پیشرفت خوبی در «مطالعه کتاب» داشتی، اما چند هدف هنوز شروع نشده‌اند. اگر هر روز ۲۰ دقیقه برای یکی از آن‌ها وقت بگذاری، تعادل بهتری بین هدف‌ها ایجاد می‌شود.

            مثال ۳ (عملکرد ضعیف):
            این هفته مطابق برنامه پیش نرفت، اما هنوز فرصت جبران وجود دارد. هفته آینده فقط روی مهم‌ترین هدفت تمرکز کن و از قدم‌های کوچک شروع کن
            Prompt)
                        ->withPrompt(json_encode($weekStats, JSON_UNESCAPED_UNICODE))
                        ->withClientRetry(3, 3000)
                        ->asText();

                    return str_replace(
                        ['\\u200c', '\\u{200C}'],
                        '‌',
                        $response->text
                    );
                } catch (\Throwable) {
                    return 'خلاصه این هفته در حال حاضر در دسترس نیست.';
                }
            }
        );
    }

    private function strongestGoal(WeeklyStatsData $weekStats): InfoItem
    {
        $goal = $weekStats->goalStats
            ->sortByDesc('completionPercentage')
            ->first();

        if ($goal === null || $goal->spentMinutes === 0) {
            return new InfoItem(
                title: 'هنوز هدفی ثبت نشده',
                description: 'این هفته هنوز زمانی برای هیچ هدفی ثبت نکرده‌ای.'
            );
        }

        return new InfoItem(
            title: $goal->goal->name,
            description: sprintf(
                '%d%% از زمان برنامه‌ریزی‌شده انجام شده',
                round($goal->completionPercentage)
            )
        );
    }

    private function weakestGoal(WeeklyStatsData $weekStats): InfoItem
    {
        $goal = $weekStats->goalStats
            ->filter(fn ($goal) => $goal->plannedMinutes > 0)
            ->sortBy('completionPercentage')
            ->first();

        if ($goal === null) {
            return new InfoItem(
                title: 'موردی وجود ندارد',
                description: 'برای این هفته هدفی برنامه‌ریزی نشده است.'
            );
        }

        if ($goal->spentMinutes === 0) {
            return new InfoItem(
                title: $goal->goal->name,
                description: sprintf(
                    'هنوز شروع نشده — %d دقیقه برنامه‌ریزی شده',
                    round($goal->plannedMinutes)
                )
            );
        }

        return new InfoItem(
            title: $goal->goal->name,
            description: sprintf(
                '%d%% پیشرفت — %d دقیقه باقی مانده',
                round($goal->completionPercentage),
                round($goal->remainingMinutes)
            )
        );
    }

    private function weekOffer(WeeklyStatsData $weekStats): InfoItem
    {
        $goal = $weekStats->goalStats
            ->filter(fn ($goal) => $goal->plannedMinutes > 0)
            ->sort(function ($a, $b) {
                if ($a->completionPercentage === $b->completionPercentage) {
                    return $b->plannedMinutes <=> $a->plannedMinutes;
                }

                return $a->completionPercentage <=> $b->completionPercentage;
            })
            ->first();

        if ($goal === null) {
            return new InfoItem(
                title: 'پیشنهادی وجود ندارد',
                description: 'این هفته هنوز هدفی برای برنامه‌ریزی ثبت نشده است.'
            );
        }

        if ($goal->completionPercentage >= 100) {
            return new InfoItem(
                title: 'همین روند را حفظ کن',
                description: 'تمام هدف‌ها طبق برنامه پیش می‌روند.'
            );
        }

        $minutes = min(30, max(15, round($goal->remainingMinutes / 7)));

        return new InfoItem(
            title: 'روی «'.$goal->goal->name.'» تمرکز کن',
            description: "هر روز حدود {$minutes} دقیقه برای این هدف زمان بگذار."
        );
    }

    private function weeklyProgress(User $user): InfoItem
    {
        $currentWeek = $this->weekRepository->getCurrentWeek(
            $user,
            config('week.default_planned_minutes')
        );

        $previousWeek = $this->weekRepository->getPreviousWeek($user);

        if ($previousWeek === null) {
            return new InfoItem(
                title: 'اولین هفته',
                description: 'هنوز اطلاعاتی از هفته قبل وجود ندارد.'
            );
        }

        $currentSpent = $this->timeLogRepository->getSpentMinutes(
            $user,
            $currentWeek->week_start_date,
            Jalalian::fromFormat('Y-m-d', $currentWeek->week_start_date)
                ->getEndDayOfWeek()
                ->format('Y-m-d')
        );

        $previousSpent = $this->timeLogRepository->getSpentMinutes(
            $user,
            $previousWeek->week_start_date,
            Jalalian::fromFormat('Y-m-d', $previousWeek->week_start_date)
                ->getEndDayOfWeek()
                ->format('Y-m-d')
        );

        if ($currentSpent === $previousSpent) {
            return new InfoItem(
                title: 'بدون تغییر',
                description: 'عملکردت نسبت به هفته قبل تقریباً ثابت بوده است.'
            );
        }

        if ($previousSpent === 0) {
            return new InfoItem(
                title: 'شروع خوب',
                description: 'نسبت به هفته قبل فعالیت بیشتری ثبت کرده‌ای.'
            );
        }

        $difference = $currentSpent - $previousSpent;
        $percentage = round(abs($difference) / $previousSpent * 100);

        if ($difference > 0) {
            return new InfoItem(
                title: 'رو به بهبود',
                description: "{$percentage}٪ بیشتر از هفته قبل فعالیت داشتی."
            );
        }

        return new InfoItem(
            title: 'افت عملکرد',
            description: "{$percentage}٪ کمتر از هفته قبل فعالیت داشتی."
        );
    }

    private function compares(User $user)
    {
        $currentWeek = $this->weekRepository->getCurrentWeek(
            $user,
            config('week.default_planned_minutes')
        );

        $previousWeek = $this->weekRepository->getPreviousWeek($user);

        if ($previousWeek === null) {
            return collect();
        }

        $currentSpent = $this->timeLogRepository->getSpentMinutesByGoal(
            $user,
            $currentWeek->week_start_date,
            Jalalian::fromFormat('Y-m-d', $currentWeek->week_start_date)
                ->getEndDayOfWeek()
                ->format('Y-m-d')
        );

        $previousSpent = $this->timeLogRepository->getSpentMinutesByGoal(
            $user,
            $previousWeek->week_start_date,
            Jalalian::fromFormat('Y-m-d', $previousWeek->week_start_date)
                ->getEndDayOfWeek()
                ->format('Y-m-d')
        );

        return $user->goals
            ->map(function ($goal) use (
                $currentWeek,
                $previousWeek,
                $currentSpent,
                $previousSpent
            ) {

                $thisWeekPlanned = (int) round(
                    $goal->priority_percentage * $currentWeek->planned_minutes / 100
                );

                $lastWeekPlanned = (int) round(
                    $goal->priority_percentage * $previousWeek->planned_minutes / 100
                );

                $thisWeekSpent = (int) ($currentSpent[$goal->id] ?? 0);
                $lastWeekSpent = (int) ($previousSpent[$goal->id] ?? 0);

                return new CompareItem(
                    goalName: $goal->name,
                    lastWeekPlannedMinutes: $lastWeekPlanned,
                    lastWeekSpentMinutes: $lastWeekSpent,
                    thisWeekPlannedMinutes: $thisWeekPlanned,
                    thisWeekSpentMinutes: $thisWeekSpent,
                    differenceMinutes: $thisWeekSpent - $lastWeekSpent
                );
            })
            ->sortByDesc(fn (CompareItem $item) => abs($item->differenceMinutes))
            ->values();
    }
}
