---
sidebar_position: 1
title: Introduction
---

# Local Development

:::caution

This documentation assumes a strong grounding in both client side and server side Javascript, as well as Typescript

:::

## The Repo

The repo is a [monorepo](https://www.atlassian.com/git/tutorials/monorepos), containing three distinct applications:

Directory|Description||
:-----|:-----|:----
`/api`|NestJS API|[Docs](./api/intro)
`/app`|React App|[Docs](./app/intro)
`/docs`|Public Facing Marketing & Documentation Website|[Docs](./docs/intro)
`/assets`|*Logos/Marketing Assets*|

## Visual Studio Code

[VSCode](https://github.com/microsoft/vscode) is recommended for development, and debug profiles are provided for all three applications as part of the workspace file. 

:::tip

Be sure to run `npm install` first before attempting to use the debug profiles

:::
 