from socket import *
serverName = 'localhost'
serverPort = 12000
clientSocket = socket(AF_INET, SOCK_DGRAM) #ipv4, udp

while True:
    message = input("mesasggio: ")
    if message == "END":
        clientSocket.close()
        break
    clientSocket.sendto(message.encode(),(serverName, serverPort)) #default message encode utf8
    modifiedMessage, serverAddress = clientSocket.recvfrom(2048) #buffer in bytes
    print(modifiedMessage.decode())