# ln

Create links to files and directories.

## examples

There are some examples for use `ln`

Create a symbolic link to a file or directory:

```bash
  ln -s path/to/file_or_directory path/to/symlink
```

Overwrite an existing symbolic link to point to a different file:

```bash
  ln -sf path/to/new_file path/to/symlink
```

Create a hard link to a file:

```bash
  ln path/to/file path/to/hardlink
```
