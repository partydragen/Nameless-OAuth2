<?php

class UserUpdatedListener {
    public static function execute(UserUpdatedEvent $event): void {
        OAuth2::syncUserApplications($event->user);
    }
}