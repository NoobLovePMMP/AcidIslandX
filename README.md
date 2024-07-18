
<img src="img/acidisland.png"></img>
# Information Plugin
```YAML
name: AcidIslandX
api: 5.0.0
version: 1.0.0
author: NoobMCGaming ( NoobLovePMMP )
```
# Wiki For You :3
## How to set up starter item ?
In ```manager.yml```, you will see..
```YAML
item-starter: []
```
You can add items using the following syntax:
```YAML
item-starter:
- <item>:<count>:<name>
```
Example:
```YAML
item-starter:
- diamond_pickaxe:1:Default
- diamond_sword:1:NoobLovePMMP
```
You can use ```Default``` to use vanilla name of it

## How to set up points when placing blocks?
In ```manager.yml```, you will see..
```YAML
point-block: []
```
You can add blocks using the following syntax:
```YAML
point-block:
- <block name>:<points>
```
Example:
```YAML
point-block:
- diamond_block:5
- coal_ore:1
```

## What is command to use this plugin ?
```YAML
commands: /island, /acidisland
```

## The difference between AcidIsland plugins and my AcidIsland plugin
In this plugin you don't need to use ```FormAPI```, Because I have integrated formapi into the plugin, it means you do not need to install any libs or plugins related to ```FormAPI``` to work. But you still need to install ```MultiWorld``` <br>
  Link To Download Multiworld: <a href="https://github.com/CzechPMDevs/MultiWorld">Click Here
