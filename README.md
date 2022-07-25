# Game Status Discord Bot

> #### Fork Notice/Disclaimer
>
> This repository is a **Fork** of the [original repository](https://github.com/Ramzi-Sah/game-status-discordbot).  All thanks to the past contributors.
>
> Copyright © 2021 Ramzi-Sah, © 2022-present Clemie McCartney

Since the original repository is not maintained anymore (cmiiw), I decided to maintain a fork of it with a Full documentations and fixes.

## Overview

This repo is consisted into 2 monorepos:

- `bot/`  Bot Instances of the Game Status
- `dashboard/`  The Bot Frontend dashboard

## Setting up the databases

Before you run any of these monorepos, you need to add the provided SQL's to run the bot properly.

Add the `db.sql` inside *root* folder to your MySQL/MariaDB, then you can proceed to development.

Don't forget to sync your mysql/mariadb details with the bot & frontend.

- Bot = `bot/src/config.json`
- Frontend = `dashboard/php/db_config.php`

> Note: You might need to rename the file from `db_config.example.php` to `db_config.php` in order to get the app working. and also `config.example.json` to `config.json` too!

## Discord Oauth2 & Bot Token

We're using Discord Oauth2 for authenicating with our services. To create the application, Head over to https://discord.com/developers/applications and create a new app.

Then, Go to your app, then Select Oauth2 tabs, select the general menu, then copy the Client ID and Client Secret. Paste it to `dashboard/php/config.php`

> You might need to reset your Client Secret to generate a new Client Secret.

> You might need to rename the file from `config.example.php` to `config.php` in order to get the app working.

While you're at your discord app, Go to Bot tabs, and create a new bot.

Reset your bot token to get the token, copy the token and paste it to `bot/src/config.json`.

To invite your bot, head over to Oauth2 tabs, and select URL generator menu. Generate the `bot` scopes with `applications.commands` and select your preferred permissions. Copy the provided oauth2 links and invite the bot to your server.

## Frontend Development

To work on the frontend, you mostly only need to focus on the `dashboard/dashboard` directory. Spin up a Apache2/NGINX server and put the `dashboard/` directory to it. See the readme file in there for more details.

## Bot Development

When working on the bot, the `bot/src` directory is where you need to be. The bot application will assume it's being run from the `bot/`, _not_ from within `bot/src`.

Before you start the bot, you need to setup the dependencies, use [yarn](https://yarnpkg.com/) and run
`yarn`. To start the bot, type `yarn start`.

## Docker

TBA. Stay Tune!

## Questions?

Got any other questions? Shoot your thoughts to my [Discord](https://discord.com/users/351150966948757504).
