<?php

use DigitallyHappy\Assets\Enums\StatusEnum;

test('confirm environment is set to testing', function () {
    expect(config('app.env'))->toBe('testing');
});

it('fails on invalid path', function () {
    $result = basset('invalid path', false);

    expect($result)->toBe(StatusEnum::INVALID);
});

it('cleans the pathname of an asset', function ($asset, $path) {
    $generatedPath = basset()->getAssetPath($asset);

    expect((string) $generatedPath)->toBe("bassets/$path");
})->with('paths');

it('downloads a cdn basset', function ($asset) {
    $result = basset($asset, false);

    Http::assertSentCount(1);

    expect($result)->toBe(StatusEnum::INTERNALIZED);
})->with('cdn');

it('stores a downloaded basset', function ($asset) {
    $result = basset($asset, false);
    $path = basset()->getAssetPath($asset);

    disk()->assertExists($path);

    expect($result)->toBe(StatusEnum::INTERNALIZED);
})->with('cdn');

it('cleans the content of a downloaded basset', function ($asset) {
    basset($asset, false);
    $path = basset()->getAssetPath($asset);

    expect(disk()->get($path))->toBe(getStub("$asset.output"));
})->with('cdn');

it('copies a local basset', function ($asset) {
    // prepare test
    disk()->put($asset, getStub($asset));
    $path = disk()->path($asset);

    $result = basset($path, false);

    Http::assertSentCount(0);

    expect($result)->toBe(StatusEnum::INTERNALIZED);
})->with('local');

it('stores a local basset', function ($asset) {
    // create the stub resource in disk
    disk()->put($asset, getStub($asset));
    $path = disk()->path($asset);

    // internalize the file
    $result = basset($path, false);
    $path = basset()->getAssetPath($path);

    disk()->assertExists($path);

    expect($result)->toBe(StatusEnum::INTERNALIZED);
})->with('local');

it('cleans the content of a local basset', function ($asset) {
    // create the stub resource in disk
    disk()->put($asset, getStub($asset));
    $path = disk()->path($asset);

    // internalize the file
    $result = basset($path, false);
    $path = basset()->getAssetPath($path);

    expect(disk()->get($path))->toBe(getStub("$asset.output"));

    expect($result)->toBe(StatusEnum::INTERNALIZED);
})->with('local');

it('does not download twice', function ($asset) {
    // first call should download
    $result = basset($asset, false);

    expect($result)->toBe(StatusEnum::INTERNALIZED);

    // second call asset should be already loaded
    $result = basset($asset);

    expect($result)->toBe(StatusEnum::LOADED);

    // only 1 call could have been made to http
    Http::assertSentCount(1);
})->with('cdn');

it('does not output when not required', function ($asset) {
    basset($asset, false);

    expect($this->getActualOutput())->toBeEmpty();
})->with('cdn');

it('retreives from cache when available', function ($asset) {
    // store the stub in disk
    $generatedPath = basset()->getAssetPath($asset);
    disk()->put($generatedPath, getStub($asset));

    // should not download
    $result = basset($asset, false);

    expect($result)->toBe(StatusEnum::IN_CACHE);

    // no call could have been made to http
    Http::assertSentCount(0);
})->with('cdn');
