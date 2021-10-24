---
sidebar_position: 20
title: Contributing
---

Thanks for you interest in contributing to the AdamRMS project!

We use [Github](https://github.com/bstudios/adam-rms/) as our source control system.

All contributions are covered by the existing project licence - please have a look at this to ensure you're familiar with it before contributing. When you contribute you'll be asked to accept the CLA (Contributor Licence Agreement).

## Versioning

AdamRMS uses semantic versioning. v2.0.0 is currently being targeted, this will bring a new RESTful API. 

To create a new release:

1. Create a new release in Github, with a tag of the semantic version number. **Tips:**
    - Make sure you are targeting the default branch, not a branch like `v1`
    - Make sure you use a lowercase `v` in the tag name
    - Make sure your release is in the format `vx.y.z` where `x`/`y`/`z` is a positive integer
1. A new Docker image for v1 will be built and pushed to Docker Hub, based on the v1 branch. *(Only applies if tag starts with v1)* 
1. A new Docker image for v1 will be built and pushed to GitHub Packages, based on the v1 branch. *(Only applies if tag starts with v1)* 
1. Cloudflare pages will update the public marketing/docs website.
1. The hosted-solution servers will pull the latest update from Github Packages to update the dashboard. *(Only applies if tag starts with v1)* 

## Using Github

We use the [Git Feature Branch Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/feature-branch-workflow) for writing new features/bug fixes etc, and for code review.

When doing so, we ask that you follow the guidelines below:

### Writing Commits

When writing commits use the **present/imperative tense**, and make sure the message is **descriptive** enough.

**✔ Good**

```
Add new integration with network printers
```

**❌ Bad**

```
Changed printers.php
```

Additionally, **please don't make too many commits**. If you can, bundle together similar changes into a single commit.


### Creating Issues

Make sure your issue has a **concise but descriptive title**, and describe your problem in more detail inside the issue.  
If you're reporting a bug, the aim is to help us reproduce it on our end so we can fix it. If you're reporting a feature/enhancement, the aim to help us visualise this feature and why it might be good for the site.

**✔ Good**

```
Projects list not loading

When on the homepage the menu doesn't show the latest list of projects.
```

**❌ Bad**

```
List broken
```

### Making Pull Requests

Similarly to commits, please write your PR title in the present/imperative tense, and describe what problem it fixes. Please ensure you name your branch in a way that relates to the issue/pull request (such as `80-fixAssetSearch` would be a good branch name).

Additionally, link the issue to the Pull Request. 

In the PR's description, it would be helpful to write a brief summary of the changes you made, too.