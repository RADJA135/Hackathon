import os
import network_as_code as nac
from crewai.tools import tool

nac_client = nac.NetworkAsCodeClient(token=os.getenv("NOKIA_APP_KEY"))

@tool("Device Status Check")
def check_device_status(phone_number: str) -> str:
    """Checks whether the device for this phone number is known/reachable on the network."""
    device = nac_client.devices.get(phone_number=phone_number)
    status = device.status.connectivity()  # confirm exact method on your NaC dashboard's Device Status product page — method names have shifted across SDK versions
    return str(status)


if __name__ == "__main__":
    print(check_device_status("+213xxxxxxxxx"))