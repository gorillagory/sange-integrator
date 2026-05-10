# Git Flow

## Branch Roles

- `pr`: integration branch for feature PRs
- `staging`: pre-release validation branch
- `production`: release-candidate branch
- `main`: final published baseline

## Promotion Path

1. Feature branch -> PR into `pr`
2. `pr` -> PR into `staging`
3. `staging` -> PR into `production`
4. `production` -> PR into `main`

No direct pushes should be allowed on `staging`, `production`, or `main`.

## Recovery Backups (Created)

- `backup/remote-pre-rollback-5bba006`
- `backup/recovered-pre-rewrite-6453312`

Keep these until the new flow is stable and validated.

## Suggested Protection Rules

For `main`, `production`, and `staging`:
- Require pull requests before merging
- Require at least 1 approving review
- Require status checks to pass
- Restrict force pushes
- Restrict branch deletion

## Hotfix Path

For urgent production fixes:

1. Branch from `production` (for example `hotfix/<name>`)
2. Merge to `production`
3. Forward-merge to `main`
4. Back-merge to `staging` and `pr` if needed to keep lines synchronized
