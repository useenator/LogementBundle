<?php

namespace Ben\LogementBundle\Twig;

class TimeAgoExtension extends \Twig_Extension {

    protected $translator;

    /**
     * Constructor method
     *
     * @param IdentityTranslator $translator
     */
    public function __construct($translator) {
        $this->translator = $translator;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('distance_of_time_in_words', array($this, 'distanceOfTimeInWordsFilter')),
            new \Twig_SimpleFilter('time_ago_in_words', array($this, 'timeAgoInWordsFilter')),
        );
    }

    /**
     * Like distance_of_time_in_words, but where to_time is fixed to timestamp()
     *
     * @param $from_time String or DateTime
     * @param bool $include_seconds
     *
     * @return mixed
     */
    function timeAgoInWordsFilter($from_time, $include_seconds = false) {
        return $this->distanceOfTimeInWordsFilter($from_time, new \DateTime('now'), $include_seconds);
    }

    /**
     * Reports the approximate distance in time between two times given in seconds
     * or in a valid ISO string like.
     * For example, if the distance is 47 minutes, it'll return
     * "about 1 hour". See the source for the complete wording list.
     *
     * Integers are interpreted as seconds. So, by example to check the distance of time between
     * a created user an it's last login:
     * {{ user.createdAt|distance_of_time_in_words(user.lastLoginAt) }} returns "less than a minute".
     *
     * Set include_seconds to true if you want more detailed approximations if distance < 1 minute
     *
     * @param $from_time String or DateTime
     * @param $to_time String or DateTime
     * @param bool $include_seconds
     *
     * @return mixed
     */
    public function distanceOfTimeInWordsFilter($from_time, $to_time = null, $include_seconds = false) {
        $datetime_transformer = new \Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer(null, null, 'Y-m-d H:i:s');
        $timestamp_transformer = new \Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer();

        $from = $from_time;

        # Transforming to Timestamp
        if (!($from_time instanceof \DateTime) && !is_numeric($from_time)) {
            $from_time = $datetime_transformer->reverseTransform($from_time);
            $from_time = $timestamp_transformer->transform($from_time);
        } elseif ($from_time instanceof \DateTime) {
            $from_time = $timestamp_transformer->transform($from_time);
        }

        $to_time = empty($to_time) ? new \DateTime('now') : $to_time;

        # Transforming to Timestamp
        if (!($to_time instanceof \DateTime) && !is_numeric($to_time)) {
            $to_time = $datetime_transformer->reverseTransform($to_time);
            $to_time = $timestamp_transformer->transform($to_time);
        } elseif ($to_time instanceof \DateTime) {
            $to_time = $timestamp_transformer->transform($to_time);
        }

        $distance_in_minutes = round((abs($to_time - $from_time)) / 60);
        $distance_in_seconds = round(abs($to_time - $from_time));

        if ($distance_in_minutes <= 1) {
                if ($distance_in_seconds < 50) {
                    return $this->translator->trans('%seconds% seconds ago', array('%seconds%' => $distance_in_seconds));
                } else {
                    return $this->translator->trans('1 minute ago');
                }
        } elseif ($distance_in_minutes <= 45) {
            return $this->translator->trans('%minutes% minutes ago', array('%minutes%' => $distance_in_minutes));
        } elseif ($distance_in_minutes <= 90) {
            return $this->translator->trans('about 1 hour ago');
        } elseif ($distance_in_minutes <= 1440) {
            return $this->translator->trans('about %hours% hours ago', array('%hours%' => round($distance_in_minutes / 60)));
        } elseif ($distance_in_minutes <= 2880) {
            return $this->translator->trans('yesterday at %time%', array('%time%' => $from->format('H:i')));
        } else {
            return $from->format('\\L\\e d M à H:i');
        }
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'time_ago_extension';
    }

}