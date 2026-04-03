# BRP Website — Claude Context

## Database Schema

### `game_log`
Tracks individual poker game sessions per user.

| Column | Type | Notes |
|--------|------|-------|
| id | int | Primary key, auto-increment |
| alexa_id | varchar | Alexa user identifier |
| result | varchar | `win` or `loss` |
| rounds | int | Number of rounds played |
| chips_start | int | Chip count at start of game |
| chips_end | int | Chip count at end of game |
| net_chips | int | Difference (chips_end - chips_start) |
| played_at | datetime | Timestamp of game |

### `name_regen_log`
Tracks how many times a user has regenerated their username on a given day.

| Column | Type | Notes |
|--------|------|-------|
| id | int | Primary key, auto-increment |
| alexa_id | varchar | Alexa user identifier |
| regen_date | date | Date of regeneration |
| regen_count | int | Number of regens on that date |

### `user_names`
Stores assigned usernames for each Alexa user.

| Column | Type | Notes |
|--------|------|-------|
| id | int | Primary key, auto-increment |
| alexa_id | varchar | Alexa user identifier |
| city_name | varchar | First part of generated name (e.g. "Waldron") |
| suffix | varchar | Second part of generated name (e.g. "Lucky") |
| full_name | varchar | Concatenated display name (e.g. "Waldron Lucky") |
| assigned_at | datetime | When the name was assigned |
| released_at | datetime | When the name was released/replaced (NULL if active) |
| is_active | tinyint | 1 = current active name, 0 = retired |
