name: Run lint checks potentially affecting projects across the monorepo
on: pull_request
concurrency:
  group: changelogger-${{ github.event_name }}-${{ github.ref }}
  cancel-in-progress: true
jobs:
  changelogger_used:
    name: Changelogger use
    runs-on: ubuntu-latest
    timeout-minutes: 5
    steps:
      - uses: actions/checkout@v2
        with:
          ref: ${{ github.event.pull_request.head.sha }}
          fetch-depth: 10

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'

      - name: Check change files are touched for touched projects
        env:
          BASE: ${{ github.event.pull_request.base.sha }}
          HEAD: ${{ github.event.pull_request.head.sha }}
        run: php tools/monorepo/check-changelogger-use.php --debug "$BASE" "$HEAD"