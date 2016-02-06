' REM FreeNATS Windows Node Bogeroo
'
' This file is part of FreeNATS
'
' FreeNATS is (C) Copyright 2008-2010 PurplePixie Systems
'
' FreeNATS is free software: you can redistribute it and/or modify
' it under the terms of the GNU General Public License as published by
' the Free Software Foundation, either version 3 of the License, or
' (at your option) any later version.
'
' FreeNATS is distributed in the hope that it will be useful,
' but WITHOUT ANY WARRANTY; without even the implied warranty of
' MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
' GNU General Public License for more details.
'
' You should have received a copy of the GNU General Public License
' along with FreeNATS.  If not, see www.gnu.org/licenses
'
' For more information see www.purplepixie.org/freenats
'
' Release: 3 Alpha 06/04/2010

PushData=0
PullData=1
PushURL=""
NodeID=""
NodeKey="" 'for post only
strComputer="."
wmiUser=""
wmiPass=""
wmiDomain=""
wmiSimple=1

Set colNamedArguments= WScript.Arguments.Named

If colNamedArguments.Exists("nooutput") Then
	PullData=0
End If
If colNamedArguments.Exists("pushurl") Then
	PushData=1
	PushURL=colNamedArguments.Item("pushurl")
End If
If colNamedArguments.Exists("nodeid") Then
	NodeID=colNamedArguments.Item("nodeid")
End If
If colNamedArguments.Exists("nodekey") Then
	NodeKey=colNamedArguments.Item("nodekey")
End If
If colNamedArguments.Exists("host") Then
	strComputer=colNamedArguments.Item("host")
End If
If colNamedArguments.Exists("user") Then
	wmiUser=colNamedArguments.Item("user")
	wmiSimple=0
End If
If colNamedArguments.Exists("pass") Then
	wmiPass=colNamedArguments.Item("pass")
End If
If colNamedArguments.Exists("domain") Then
	wmiDomain=colNamedArguments.Item("domain")
	wmiSimple=0
End If

OutputBuffer = ""

OB "<?xml version=""1.0"" encoding=""UTF-8""?>"
OB "<freenats-data>"
OB " <header>"
OB "  <name>FreeNATS VBS Node XML</name>"
OB "  <version>0.03</version>"
OB " </header>"






Function OB(text)
	OutputBuffer = OutputBuffer & text & vbCrLf
End Function

Function DumpOB()
	Wscript.Echo OutputBuffer
End Function

alloweds = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890!$%^*()[]{}?/+=-_ "

Function SafeString(text)
	text = Replace(text,"&","+")
	output = ""
	For position = 1 to Len(text)
		character = Mid(text,position,1)
		pos = InStr(alloweds,character)
		If pos > 0 Then
			output = output & character
		Else
			output = output & "_"
		End If
	Next
	SafeString = output
End Function

If wmiSimple = 1 Then
 Set objWMIService = GetObject("winmgmts:" & "{impersonationLevel=impersonate}!\\" & strComputer & "\root\cimv2")
Else
 set objLocator = CreateObject("WbemScripting.SWbemLocator")
 set objWMIService = objLocator.ConnectServer(strComputer,"root/cimv2",wmiUser,wmiPass)
 objWMIService.Security_.impersonationlevel=3 ' at level 3 you are impersonating the pope
End If


set objRefresher = CreateObject("WbemScripting.Swbemrefresher")
Set objProcessor = objRefresher.AddEnum (objWMIService, "Win32_PerfFormattedData_PerfOS_Processor").objectSet

Set colRunningServices = objWMIService.ExecQuery("Select * from Win32_Service")
Set memItems = objRefresher.AddEnum (objWMIService, "Win32_PerfFormattedData_PerfOS_Memory").objectSet
Set colOperatingSystems = objWMIService.ExecQuery ("Select * from Win32_OperatingSystem")

Set colRunningProcesses = objWMIService.ExecQuery("Select * from Win32_Process")

Set WshNetwork = WScript.CreateObject("WScript.Network")
AddTest "system.name","Computer Name",SafeString(WshNetwork.ComputerName),0


objRefresher.Refresh ' No I don't really get it either
WScript.Sleep 2000   ' but unless you do this you get even
objRefresher.Refresh ' weirder than usual values.

int ServiceRunning=0
int Alert=0
For Each objService in colRunningServices 
	If objService.State = "Running" Then
		ServiceRunning=1
		Alert=0
	Else
		Alert=1
		ServiceRunning=0
	End If
	TName = Replace(objService.Name," ","_")
	TestName="srv." & SafeString(TName)
    AddTest TestName, SafeString(objService.DisplayName) & " Service", ServiceRunning, Alert
Next


intThresholdViolations = 0


Set colDisks = objWMIService.ExecQuery ("Select * from Win32_LogicalDisk")

For Each objDisk in colDisks

DiskSpace = objDisk.FreeSpace / 1024 / 1024
DiskSize = objDisk.Size / 1024 / 1024
DiskUsed = DiskSize - DiskSpace
DiskUsedPerc = ( DiskUsed / DiskSize ) * 100
DiskInDrive = 1
If IsNull(DiskSpace)  Then
 DiskSpace = "0"
 DiskUsed = "0"
 DiskUsedPerc = "0.00"
 DiskInDrive = 0
End If
DiskSpace = Round(DiskSpace,2)
DiskUsed = Round(DiskUsed,2)
DiskUsedPerc = Round(DiskUsedPerc,2)

alertlevel = 0
If DiskUsedPerc > 80 Then
 alertlevel = 1
End If
If DiskUsedPerc > 90 Then
 alertlevel = 2
End If


If DiskInDrive = 1 Then
TestName= "disk.free." & Left(objDisk.DeviceID,1)
AddTest TestName,"Space Free On " & objDisk.DeviceID & " (Mb)", DiskSpace, alertlevel
TestName= "disk.used." & Left(objDisk.DeviceID,1)
AddTest TestName,"Space Used On " & objDisk.DeviceID & " (Mb)", DiskUsed, alertlevel
TestName= "disk.used.perc." & Left(objDisk.DeviceID,1)
AddTest TestName,"Percent Disk Usage On " & objDisk.DeviceID & " (%)", DiskUsedPerc, alertlevel
End If

Next



For Each objItem in objProcessor

    If objItem.Name = "_Total" Then
    	AddTest "proc.load","Processor Load (%)",objItem.PercentProcessorTime,0
	End If
Next


 
For Each objOS in colOperatingSystems
    dtmBootup = objOS.LastBootUpTime
    dtmLastBootupTime = WMIDateStringToDate(dtmBootup)
    dtmSystemUptime = DateDiff("h", dtmLastBootUpTime, Now)
    AddTest "uptime","Uptime (Hours)",dtmSystemUptime,0
Next
 
Function WMIDateStringToDate(dtmBootup)
    WMIDateStringToDate = CDate(Mid(dtmBootup, 5, 2) & "/" & _
        Mid(dtmBootup, 7, 2) & "/" & Left(dtmBootup, 4) _
            & " " & Mid (dtmBootup, 9, 2) & ":" & _
                Mid(dtmBootup, 11, 2) & ":" & Mid(dtmBootup,13, 2))
End Function




For Each objItem in memItems
	TestName="mem."
	If Len(objItem.Name) > 0 Then
		TestName = TestName & objItem.Name & "."
	End If
	TestName = TestName & "free"
	
	AddTest TestName,"Memory Available (Kilobytes)",objItem.AvailableKBytes,0

Next

' Running Processes

Dim ProcArr(1024)
Dim CountArr(1024)
ProcCount=0
pName=""
pDesc=""
ProcCount = 0

 For Each objProc in colRunningProcesses
  'Wscript.Echo objProc.ProcessID & " " & objProc.Name & vbCrLf
  pName = objProc.Name
  NewProc = 1 ' Assume New Until Checked
  FoundPos = 0
  For ProcTmp = 0 To ProcCount
   If ( ProcArr(ProcTmp) = pName ) Then ' Exists
    NewProc = 0
    FoundPos = ProcTmp
    'ProcTmp = UBound(ProcArr)+1
   End If
  Next

  If NewProc = 0 Then ' Not a New Process
    CountArr(FoundPos) = CountArr(FoundPos) + 1 
   Else ' Does Not Exist
    ProcArr(ProcCount) = pName
    CountArr(ProcCount) = 1
    ProcCount = ProcCount + 1
   End If

  
 Next


For ProcTmp = 0 To ProcCount-1
' Wscript.Echo ProcArr(ProcTmp) & " " & CountArr(ProcTmp)
 AddTest "proc." & SafeString(ProcArr(ProcTmp)), ProcArr(ProcTmp) & " Process", CountArr(ProcTmp), 0
Next

'Set listProc = Nothing
'set objWMIService = Nothing



' Close Off

OB "</freenats-data>"

Function AddTest(testname,testdesc,testvalue,alertlevel)
 OB "<test NAME=""" & testname & """>"
 OB " <name>" & testname & "</name>"
 OB " <desc>" & testdesc & "</desc>"
 OB " <value>" & testvalue & "</value>"
 OB " <alertlevel>" & alertlevel & "</alertlevel>"
 OB "</test>"
End Function

If PullData=1 Then
	DumpOB()
End If

If PushData=1 Then
	' Push Da Data
	WScript.Echo
	WScript.Echo "Pushing Data..."
	WScript.Echo "URL   : " & PushURL
	WScript.Echo "NodeID: " & NodeID
	WScript.Echo "Key   : " & NodeKey
	
	Set objHTTP = CreateObject("Microsoft.XMLHTTP")
	objHTTP.open "POST", PushURL, False
	 
	objHTTP.setRequestHeader "Content-Type", "application/x-www-form-urlencoded"
	http_request = "nodeid=" & Escape(NodeID) & "&nodekey=" & Escape(NodeKey) & "&xml=" & Escape(OutputBuffer)
	objHTTP.send http_request
	 
	response = objHTTP.responseText
	 
	Set objHTTP = Nothing
	
	If Right(response,1) = "1" Then
		WScript.Echo "Post Successful"
	Else
		WScript.Echo "Encountered An Error:"
		WScript.Echo response
	End If
	
End If