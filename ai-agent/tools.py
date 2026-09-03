from datetime import datetime, timezone


def verify_location(
    current_country: str,
    expected_country: str,
    last_location_time: str | None = None,
) -> dict:
    """
    Verify whether the current location is consistent
    with the expected/known location.

    Returns:
        verificationResult: TRUE or FALSE
        lastLocationTime: timestamp of the last known location
    """

    # Normalize the country values so that
    # "fr", "FR", and " Fr " are treated the same.
    current = current_country.strip().upper()
    expected = expected_country.strip().upper()

    # Compare the two locations.
    location_matches = current == expected

    # If we were given a last-location timestamp,
    # keep it. Otherwise generate the current UTC time.
    if last_location_time:
        timestamp = last_location_time
    else:
        timestamp = datetime.now(timezone.utc).isoformat()

    return {
        "verificationResult": "TRUE" if location_matches else "FALSE",
        "lastLocationTime": timestamp,
    }

if __name__ == "__main__":
    result = verify_location(
        current_country="US",
        expected_country="FR",
        last_location_time="2026-09-02T18:18:55.542906",
    )

    print(result)