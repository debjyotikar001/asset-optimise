<?php

namespace Debjyotikar001\AssetOptimise\Listeners;

use hexydec\html\htmldoc;
use Symfony\Component\Mime\Part\TextPart;
use Illuminate\Mail\Events\MessageSending;

class EmailMinifier
{
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(MessageSending $event): void
  {
    if (config('assetoptimise.email_enabled')) {
      $message = $event->message;
      $html = $message->getBody()->getBody();

      // minify
      $doc = new htmldoc();
      $doc->load($html);
      $doc->minify();
      $html = $doc->save();

      $html = new TextPart($html, 'utf-8', 'html');

      $message->setBody($html);
    }
  }
}