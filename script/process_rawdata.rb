#!/usr/bin/ruby

require "mysql"
load "config.rb"  

def quote (str)
  str.gsub("\\","\\\\\\\\").gsub("'","\\\\'")
end

load "assetfinder_module/rawdata.rb"
load "assetfinder_module/mainloop.rb"
  
app = AssetFinder::MainLoop.new
app.initializeDB(AssetFinder::Config::MYSQL[:host], AssetFinder::Config::MYSQL[:user], AssetFinder::Config::MYSQL[:password], AssetFinder::Config::MYSQL[:database])
#app.truncateTables
app.createReferenceArrayWhileLoopingThroughRawdataFolder
app.processReferenceArray
