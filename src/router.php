<?php

use RusBios\MediaHub\Controllers;

Controllers\Auth::route();
Controllers\Api\Files::route();
Controllers\Api\Albums::route();
Controllers\Api\Storage::route();
