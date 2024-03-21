<?php

namespace Ophose;

class Duration {

    private int $seconds;

    /**
     * Duration constructor.
     *
     * @param int $seconds The duration in seconds.
     */
    public function __construct(int $seconds) {
        $this->seconds = $seconds;
    }

    /**
     * Get the duration in seconds.
     *
     * @return int The duration in seconds.
     */
    public function getSeconds(): int {
        return $this->seconds;
    }

    /**
     * Get the duration in minutes.
     *
     * @return int The duration in minutes.
     */
    public function getMinutes(): int {
        return $this->seconds / 60;
    }

    /**
     * Get the duration in hours.
     *
     * @return int The duration in hours.
     */
    public function getHours(): int {
        return $this->seconds / 3600;
    }

    /**
     * Get the duration in days.
     *
     * @return int The duration in days.
     */
    public function getDays(): int {
        return $this->seconds / 86400;
    }

    /**
     * Get the duration in weeks.
     *
     * @return int The duration in weeks.
     */
    public function getWeeks(): int {
        return $this->seconds / 604800;
    }

    /**
     * Get the duration in months.
     *
     * @return int The duration in months.
     */
    public function getMonths(): int {
        return $this->seconds / 2628000;
    }

    /**
     * Get the duration in years.
     *
     * @return int The duration in years.
     */
    public function getYears(): int {
        return $this->seconds / 31536000;
    }

    /**
     * Get the string representation of the duration.
     *
     * @return string The string representation of the duration.
     */
    public function __toString(): string {
        $years = $this->getYears();
        $months = $this->getMonths();
        $weeks = $this->getWeeks();
        $days = $this->getDays();
        $hours = $this->getHours();
        $minutes = $this->getMinutes();
        $seconds = $this->seconds;
        $string = '';
        if($years > 0) {
            $string .= $years . ' year' . ($years > 1 ? 's' : '') . ' ';
        }
        if($months > 0) {
            $string .= $months . ' month' . ($months > 1 ? 's' : '') . ' ';
        }
        if($weeks > 0) {
            $string .= $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ';
        }
        if($days > 0) {
            $string .= $days . ' day' . ($days > 1 ? 's' : '') . ' ';
        }
        if($hours > 0) {
            $string .= $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ';
        }
        if($minutes > 0) {
            $string .= $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ';
        }
        if($seconds > 0) {
            $string .= $seconds . ' second' . ($seconds > 1 ? 's' : '') . ' ';
        }
        return trim($string);
    }

    /**
     * Create a Duration object from milliseconds.
     *
     * @param int $milliseconds The duration in milliseconds.
     * @return Duration The Duration object.
     */
    public static function milliseconds(int $milliseconds): Duration {
        return new Duration($milliseconds / 1000);
    }

    /**
     * Create a Duration object from seconds.
     *
     * @param int $seconds The duration in seconds.
     * @return Duration The Duration object.
     */
    public static function seconds(int $seconds): Duration {
        return new Duration($seconds);
    }

    /**
     * Create a Duration object from minutes.
     *
     * @param int $minutes The duration in minutes.
     * @return Duration The Duration object.
     */
    public static function minutes(int $minutes): Duration {
        return new Duration($minutes * 60);
    }

    /**
     * Create a Duration object from hours.
     *
     * @param int $hours The duration in hours.
     * @return Duration The Duration object.
     */
    public static function hours(int $hours): Duration {
        return new Duration($hours * 3600);
    }

    /**
     * Create a Duration object from days.
     *
     * @param int $days The duration in days.
     * @return Duration The Duration object.
     */
    public static function days(int $days): Duration {
        return new Duration($days * 86400);
    }

    /**
     * Create a Duration object from weeks.
     *
     * @param int $weeks The duration in weeks.
     * @return Duration The Duration object.
     */
    public static function weeks(int $weeks): Duration {
        return new Duration($weeks * 604800);
    }

    /**
     * Create a Duration object from months.
     *
     * @param int $months The duration in months.
     * @return Duration The Duration object.
     */
    public static function months(int $months): Duration {
        return new Duration($months * 2628000);
    }

    /**
     * Create a Duration object from years.
     *
     * @param int $years The duration in years.
     * @return Duration The Duration object.
     */
    public static function years(int $years): Duration {
        return new Duration($years * 31536000);
    }
}