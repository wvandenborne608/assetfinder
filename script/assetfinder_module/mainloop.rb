#!/usr/bin/ruby

module AssetFinder

  class MainLoop
    @@dbConnection
    @@arReference

    # Create database connection and store reference in global class variable "dbConnection"
    def initializeDB (host, user, pass, db)
      @@dbConnection = Mysql.new(host, user, pass, db)
    end  


    def truncateTables ()
      @@dbConnection.query("Truncate table item")
      @@dbConnection.query("Truncate table itemlinks")
      @@dbConnection.query("Truncate table itemtags")
      @@dbConnection.query("Truncate table tag")
    end


    # Loop rawdata folder and prepare reference array
    def createReferenceArrayWhileLoopingThroughRawdataFolder
      @@arReference = Hash.new{ |a,b| a[b] = Hash.new() }
      Dir.foreach(AssetFinder::Config::MAIN[:rawdataLocation]) do |item|
        next if item == '.' or item == '..'
        tmp = item.split(/_\$*?(\w+)-\$*?(.{2})_\$*?(.{2})\$*?(.*)/)
        @@arReference[tmp[0].to_i][tmp[4] ? :linked : :normal] = item
      end
    end

    # Process reference array
    def processReferenceArray
      @@arReference.sort.each do |a,b| 
        rawdata = RawData.new(b[:normal], b[:linked],@@dbConnection)
        rawdata.addToDataBase()
      end
    end

  end  

end

