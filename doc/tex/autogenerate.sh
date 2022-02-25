#!/bin/bash
# This one simply spams xelatex and evince
# It's useful when left on its own workspace, as you can close evince with Ctrl+w
#  and it'll regen your pdf and show it again.
# To exit you either kill this process or close the console

while true; do
    xelatex main.tex
    xelatex main.tex
    mkdir -p archive
    cp main.pdf archive/main_$(date +"%Y-%m-%d_%H-%M-%S").pdf
    evince main.pdf
done
