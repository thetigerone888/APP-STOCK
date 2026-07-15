const { getDefaultConfig } = require("expo/metro-config");
const { withNativeWind } = require("nativewind/metro");

const config = getDefaultConfig(__dirname);

module.exports = withNativeWind(config, {
  input: "./global.css",
  // Force write CSS to file system instead of virtual modules.
  // This fixes iOS styling issues in development mode, but breaks
  // `expo export` (Metro hashes the cache file mid-write), so it's
  // only enabled for `expo start` (NODE_ENV=development).
  forceWriteFileSystem: process.env.NODE_ENV === "development",
});
