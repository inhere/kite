#!/usr/bin/env -S kotlinc -script
// kotlinc -script list-file.kts
import java.io.File

val folders = File(args[0]).listFiles { file -> file.isDirectory() }
folders?.forEach { folder -> println(folder) }
