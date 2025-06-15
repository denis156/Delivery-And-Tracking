<?php

namespace App\Foundation;

use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

/*
                                                   .~))>>
                                                  .~)>>
                                                .~))))>>>
                                              .~))>>             ___
                                            .~))>>)))>>      .-~))>>
                                          .~)))))>>       .-~))>>)>
                                        .~)))>>))))>>  .-~)>>)>
                    )                 .~))>>))))>>  .-~)))))>>)>
                 ( )@@*)             //)>))))))  .-~))))>>)>
               ).@(@@               //))>>))) .-~))>>)))))>>)>
             (( @.@).              //))))) .-~)>>)))))>>)>
           ))  )@@*.@@ )          //)>))) //))))))>>))))>>)>
        ((  ((@@@.@@             |/))))) //)))))>>)))>>)>
       )) @@*. )@@ )   (\_(\-\b  |))>)) //)))>>)))))))>>)>
     (( @@@(.@(@ .    _/`-`  ~|b |>))) //)>>)))))))>>)>
      )* @@@ )@*     (@)  (@) /\b|))) //))))))>>))))>>
    (( @. )@( @ .   _/  /    /  \b)) //))>>)))))>>>_._
     )@@ (@@*)@@.  (6///6)- / ^  \b)//))))))>>)))>>   ~~-.
  ( @jgs@@. @@@.*@_ VvvvvV//  ^  \b/)>>))))>>      _.     `bb
   ((@@ @@@*.(@@ . - | o |' \ (  ^   \b)))>>        .'       b`,
    ((@@).*@@ )@ )   \^^^/  ((   ^  ~)_        \  /           b `,
      (@@. (@@ ).     `-'   (((   ^    `\ \ \ \ \|             b  `.
        (*.@*              / ((((        \| | |  \       .       b `.
                          / / (((((  \    \ /  _.-~\     Y,      b  ;
                         / / / (((((( \    \.-~   _.`" _.-~`,    b  ;
                        /   /   `(((((()    )    (((((~      `,  b  ;
                      _/  _/      `"""/   /'                  ; b   ;
                  _.-~_.-~           /  /'                _.'~bb _.'
                ((((~~              / /'              _.'~bb.--~
                                   ((((          __.-~bb.-~
                                               .'  b .~~
                                               :bb ,'
                                               ~~~~
 */

class Inspiring
{
    /**
     * Get an inspiring quote.
     *
     * Taylor & Dayle made this commit from Jungfraujoch. (11,333 ft.)
     *
     * May McGinnis always control the board. #LaraconUS2015
     *
     * RIP Charlie - Feb 6, 2018
     *
     * @return string
     */
    public static function quote()
    {
        return static::formatForConsole(static::quotes()->random());
    }

    /**
     * Get the collection of inspiring quotes.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function quotes()
    {
        return new Collection([
            __('inspiring.quotes.kant'),
            __('inspiring.quotes.socrates'),
            __('inspiring.quotes.naval'),
            __('inspiring.quotes.roosevelt1'),
            __('inspiring.quotes.dalai_lama'),
            __('inspiring.quotes.laozi1'),
            __('inspiring.quotes.cato'),
            __('inspiring.quotes.edison'),
            __('inspiring.quotes.marcus1'),
            __('inspiring.quotes.eliot'),
            __('inspiring.quotes.seneca1'),
            __('inspiring.quotes.seneca2'),
            __('inspiring.quotes.da_vinci1'),
            __('inspiring.quotes.franklin'),
            __('inspiring.quotes.gandhi1'),
            __('inspiring.quotes.marcus2'),
            __('inspiring.quotes.roosevelt2'),
            __('inspiring.quotes.augustine'),
            __('inspiring.quotes.marcus3'),
            __('inspiring.quotes.gerould'),
            __('inspiring.quotes.alembert'),
            __('inspiring.quotes.bledsoe'),
            __('inspiring.quotes.da_vinci2'),
            __('inspiring.quotes.thich1'),
            __('inspiring.quotes.jobs'),
            __('inspiring.quotes.seneca3'),
            __('inspiring.quotes.marcus4'),
            __('inspiring.quotes.marcus5'),
            __('inspiring.quotes.aristotle'),
            __('inspiring.quotes.laozi2'),
            __('inspiring.quotes.thich2'),
            __('inspiring.quotes.thich3'),
            __('inspiring.quotes.thich4'),
            __('inspiring.quotes.thich5'),
            __('inspiring.quotes.thich6'),
            __('inspiring.quotes.curie'),
            __('inspiring.quotes.ataturk'),
            __('inspiring.quotes.mead'),
            __('inspiring.quotes.gandhi2'),
            __('inspiring.quotes.otwell'),
        ]);
    }

    /**
     * Formats the given quote for a pretty console output.
     *
     * @param  string  $quote
     * @return string
     */
    protected static function formatForConsole($quote)
    {
        [$text, $author] = (new Stringable($quote))->explode('-');

        return sprintf(
            "\n  <options=bold>“ %s ”</>\n  <fg=gray>— %s</>\n",
            trim($text),
            trim($author),
        );
    }
}
