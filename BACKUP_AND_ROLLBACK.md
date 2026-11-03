# Local backup and rollback

Before a material platform change, create two rollback layers:

1. A pushed Git tag for the tracked source code.
2. A local, ignored Docker snapshot containing `moodle.sql`, `moodledata.tar.gz`, and `moodlecode.tar.gz`.

The source tag alone cannot restore grades, attempts, Moodle configuration, generated files, or the installed copy
of Moodle. The local snapshot is therefore required for an exact local-platform rollback.

## Current protected baseline

- Git tag: `pre-assessment-ui-and-multilingual-2026-11-04`
- Local snapshot directory: `backups/2026-11-04-pre-assessment-ui-and-multilingual/`

## Restore procedure

This is intentionally manual because restoring overwrites the current local database and Moodle volumes.

1. Stop the Compose stack.
2. Keep a new backup of the state being replaced.
3. Recreate the named volumes `csfr-lms_dbdata`, `csfr-lms_moodledata`, and `csfr-lms_moodlecode`.
4. Restore `moodle.sql` into MariaDB.
5. Extract `moodledata.tar.gz` and `moodlecode.tar.gz` into their matching volumes.
6. Check out the named Git tag, start the stack, then purge Moodle caches.

Do not delete a current volume or restore an archive without explicit owner approval.
