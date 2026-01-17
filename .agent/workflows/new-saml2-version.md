---
description: Bump version (major/minor/patch), update changelog, and tag release for beartropy/saml2.
---

1. Ask the user for the increment type: `major`, `minor`, or `patch`.

2. Run this command to update version and changelog. Replace `[TYPE]` with `major`, `minor`, or `patch`.
   // turbo
   ```bash
   php -r '$t="$argv[1]";$c=json_decode(file_get_contents("composer.json"),true);$v=explode(".",$c["version"]);if($t=="major"){$v[0]++;$v[1]=0;$v[2]=0;}elseif($t=="minor"){$v[1]++;$v[2]=0;}else{$v[2]++;}$nv=implode(".",$v);$c["version"]=$nv;file_put_contents("composer.json",json_encode($c,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");$d=date("Y-m-d");$l=file_get_contents("CHANGELOG.md");$l=preg_replace("/## \[v.*?\] - .*?\n/", "## [v$nv] - $d\n\n### Changed\n- \n\n$0", $l, 1);file_put_contents("CHANGELOG.md",$l);echo "Bumped to $nv";' [TYPE]
   ```

3. Review the changes to `composer.json` and `CHANGELOG.md`.
   // turbo
   ```bash
   git diff
   ```

4. Commit and push the changes. The commit title should be "chore: bump to v[VERSION]" and include the changelog description.
   // turbo
   ```bash
   git add .
   git commit -m ""
   git push
   ```

5. Tag the release.
   // turbo
   ```bash
   export VERSION=$(grep '"version":' composer.json | cut -d'"' -f4)
   git tag "v$VERSION"
   git push origin "v$VERSION"
   ```
