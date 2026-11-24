# Arctica / Antarctica

The Arctica / Antarctica website includes Releases, Events, About, Contact,
Links and Updates. Band details, events and polar resource links are stored in
`data/site.json`. The event archive is based on MC Peko's local event records;
the project description and first-concert recording are confirmed by the
band's Facebook page. The Releases page embeds the SoundCloud set for the first
concert. `data/updates.json` starts empty and can be populated
with the same Git history feed format used by the other sites.

Event posters come from the existing MC Peko event archive. The 2015 concert
photo comes from the earlier MC Peko photo archive, and the 2017 photo comes
from the Nikon documentation in the band archive. The Events overview displays
available photos and a calendar placeholder when no photo is available. Event
pages display the event photo above its poster when available, with a calendar
placeholder for missing posters.

## Local preview

Start the shared website workspace from its root:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\local-tools\startup.ps1"
```

Open <http://localhost:8008>.

Settings includes Play to cycle through the navigation pages, plus Prev and Next
to move between them. Selecting a page link or pressing Escape stops Play.

Contact uses the same recipient and sending address as the Pekosoft contact
form. PHP mail delivery and sender authorization must be verified on the
production host before publishing. Failed sends preserve the message.

The logo is the current interim white SVG used by Fuzz Pop. Replace it with the
original vector artwork when it becomes available.

## Production setup

The domain and SFTP destination `/home/flamenco/arcticaantarctica.com` were
verified with Check and a successful Full upload. Pushes to `main` deploy changed
runtime files automatically. Manual workflow runs default to Check, which
verifies SFTP access and the destination without uploading files.

Add `SFTP_HOST`, `SFTP_USERNAME` and `SFTP_PASSWORD` as repository Actions
secrets. Set the optional `SFTP_PORT` Actions variable if the server does not
use port 22. Run Check before a first upload. Use Full for an initial or repair
upload when all tracked runtime files need to be sent.

## Licence

This project uses the custom Source Available — All Rights Reserved licence in
[LICENCE](./LICENCE). See that file for permissions and contact details.
