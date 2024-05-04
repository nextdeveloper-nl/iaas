- Networks on compute members will be saved as network interfaces. By this way we can understand if the network exists
or not. If the network does not exist, the network interface will be created if needed.
- CD Roms will be treated as storage devices (VDI). If the CD Rom exist, the storage device will be created if needed.
- We will try to understand if the storage member are the same or not by looking at the IP address. Since IP address
is unique, we can understand if the storage member is the same or not.

  


# Usability rules
- If the user is from Turkey, they need to have the NIN number present. Otherwise they cannot use the service.
- Since we cannot know which storage member is in which storage pool, we will leave some storage members in null storage pool id while creating the storage members. The user can assign the storage member to a storage pool later.
